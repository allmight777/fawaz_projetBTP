<?php

namespace App\Http\Controllers;

use App\Mail\RfiMail;
use App\Models\AuditLog;
use App\Models\DemandeInformation;
use App\Models\DocumentTransmission;
use App\Models\Dossier;
use App\Models\Structure;
use App\Models\TransmissionDestinataire;
use App\Models\User;
use App\Notifications\Alerte;
use App\Support\SpaceMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Demande d'information (RFI) entre chefs de structure.
 *   en_attente → accordee (le destinataire partage un document) | refusee
 *   accordee   → cloturee (le demandeur confirme la réception)
 * Seuls les chefs créent une demande ; un destinataire interne (collaborateur) répond via le lien reçu par mail.
 */
class DemandeInformationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->peutViser() || $user->demandesInformationRecues()->exists(), 403, 'Réservé aux chefs de structure.');

        $recues = DemandeInformation::where('destinataire_id', $user->id)->with('demandeur.structure')->latest()->get();
        $envoyees = DemandeInformation::where('demandeur_id', $user->id)->with('destinataire.structure', 'dossier')->latest()->get();

        return view('rfi.index', compact('recues', 'envoyees', 'user'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user->peutViser(), 403, 'Seuls les chefs de structure peuvent faire une demande d\'information.');

        return view('rfi.create', ['user' => $user, 'structures' => $this->structuresDestinataires($user)]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->peutViser(), 403);

        $data = $request->validate([
            'structure_id' => 'required|integer',
            'objet' => 'required|string|max:200',
            'message' => 'required|string|max:3000',
        ], [
            'structure_id.required' => 'Choisissez la structure à qui adresser la demande.',
            'objet.required' => 'Indiquez l\'objet de la demande.',
            'message.required' => 'Décrivez l\'information ou le document recherché.',
        ]);

        // La structure doit faire partie de la liste proposée (jamais un id arbitraire) ; la demande
        // part vers le chef de cette structure.
        $structure = $this->structuresDestinataires($user)->firstWhere('id', (int) $data['structure_id']);
        $destinataire = $structure?->chefDestinataire;
        if (!$structure || !$destinataire) {
            return back()->withErrors(['structure_id' => 'Structure invalide.'])->withInput();
        }

        $demande = DemandeInformation::create([
            'demandeur_id' => $user->id,
            'destinataire_id' => $destinataire->id,
            'portee' => 'externe',
            'objet' => $data['objet'],
            'message' => $data['message'],
        ]);

        $this->mail($demande, 'demandee', $destinataire);
        Alerte::envoyer($destinataire, 'Demande d\'information de ' . ($user->structure?->nom ?? $user->full_name) . ' : ' . $demande->objet, route('rfi.show', $demande));

        return redirect()->route('rfi.show', $demande)->with('success', 'Demande envoyée à ' . $structure->nom . '. Son responsable a été prévenu par mail.');
    }

    public function show(DemandeInformation $demande)
    {
        $user = Auth::user();
        $this->verifierParticipant($demande, $user);
        $demande->load('demandeur.structure', 'destinataire.structure', 'dossier');

        $dossiers = collect();
        if ($demande->destinataire_id === $user->id && $demande->statut === 'en_attente') {
            $dossiers = Dossier::visiblePar($user)->with('documentType')->latest()->limit(300)->get();
        }

        return view('rfi.show', compact('demande', 'dossiers', 'user'));
    }

    /** Le destinataire donne accès à un document : une transmission réelle ouvre la visibilité au demandeur. */
    public function accorder(Request $request, DemandeInformation $demande)
    {
        $user = Auth::user();
        $this->verifierDestinataire($demande, $user);

        $data = $request->validate([
            'dossier_id' => 'required|integer',
            'reponse' => 'nullable|string|max:2000',
        ], ['dossier_id.required' => 'Choisissez le document à partager.']);

        $dossier = Dossier::visiblePar($user)->find($data['dossier_id']);
        if (!$dossier) {
            return back()->withErrors(['dossier_id' => 'Document introuvable ou inaccessible.'])->withInput();
        }

        DB::transaction(function () use ($demande, $dossier, $user, $data) {
            $transmission = DocumentTransmission::create([
                'dossier_id' => $dossier->id,
                'document_version_id' => $dossier->derniereVersion()?->id,
                'emetteur_id' => $user->id,
                'mode' => 'rfi',
                'commentaire' => 'Réponse à la demande d\'information : ' . $demande->objet,
                'date_transmission' => now(),
            ]);

            TransmissionDestinataire::create([
                'document_transmission_id' => $transmission->id,
                'user_id' => $demande->demandeur_id,
            ]);

            $demande->update([
                'statut' => 'accordee',
                'dossier_id' => $dossier->id,
                'reponse' => $data['reponse'] ?? null,
                'repondu_le' => now(),
            ]);

            AuditLog::enregistrer('RFI_ACCES_ACCORDE', $dossier->id, ['demande_information_id' => $demande->id]);
        });

        $this->mail($demande->fresh(), 'accordee', $demande->demandeur);
        Alerte::envoyer($demande->demandeur, 'Accès accordé : ' . $demande->objet, route('rfi.show', $demande));

        return redirect()->route('rfi.show', $demande)->with('success', 'Accès accordé. ' . $demande->demandeur->full_name . ' a été prévenu par mail.');
    }

    public function refuser(Request $request, DemandeInformation $demande)
    {
        $this->verifierDestinataire($demande, Auth::user());

        $data = $request->validate(['reponse' => 'required|string|max:2000'], ['reponse.required' => 'Indiquez un motif de refus.']);

        $demande->update(['statut' => 'refusee', 'reponse' => $data['reponse'], 'repondu_le' => now()]);

        $this->mail($demande, 'refusee', $demande->demandeur);
        Alerte::envoyer($demande->demandeur, 'Demande refusée : ' . $demande->objet, route('rfi.show', $demande));

        return redirect()->route('rfi.show', $demande)->with('success', 'Demande refusée. Le demandeur a été prévenu.');
    }

    /** Le demandeur confirme avoir reçu le document : la demande est clôturée et le destinataire prévenu. */
    public function confirmerReception(DemandeInformation $demande)
    {
        $user = Auth::user();
        abort_unless($demande->demandeur_id === $user->id, 403);
        abort_unless($demande->statut === 'accordee', 403, 'Cette demande n\'attend pas de confirmation.');

        $demande->update(['statut' => 'cloturee', 'cloture_le' => now()]);

        $this->mail($demande, 'recue', $demande->destinataire);
        Alerte::envoyer($demande->destinataire, $user->full_name . ' a bien reçu le document : ' . $demande->objet, route('rfi.show', $demande));

        return redirect()->route('rfi.show', $demande)->with('success', 'Réception confirmée. La demande est clôturée.');
    }

    // ------------------------------------------------------------------

    private function verifierParticipant(DemandeInformation $demande, User $user): void
    {
        abort_unless(in_array($user->id, [$demande->demandeur_id, $demande->destinataire_id], true), 403);
    }

    private function verifierDestinataire(DemandeInformation $demande, User $user): void
    {
        abort_unless($demande->destinataire_id === $user->id, 403);
        abort_unless($demande->statut === 'en_attente', 403, 'Cette demande a déjà reçu une réponse.');
    }

    /** Envoi de mail sans jamais bloquer le flux (même convention que le reste de l'application). */
    private function mail(DemandeInformation $demande, string $etape, User $a): void
    {
        try {
            Mail::to($a->email)->send(new RfiMail($demande, $etape));
        } catch (\Throwable $e) {
            Log::error('Erreur envoi mail RFI (' . $etape . ') à ' . $a->email . ' : ' . $e->getMessage());
        }
    }

    /**
     * Structures auxquelles on peut s'adresser : toutes les structures actives, sauf la sienne, qui ont
     * un chef actif. Chaque structure porte son chef (chefDestinataire) : c'est lui qui reçoit la demande.
     */
    private function structuresDestinataires(User $user)
    {
        $chefs = User::where('actif', true)->where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->where('categorie_role', User::CATEGORIE_RESPONSABLE)->orWhereIn('role', [User::ROLE_CHEF_LOT]);
            })
            ->whereNotNull('structure_id')
            ->orderBy('id')->get()->groupBy('structure_id');

        return Structure::actives()->orderBy('nom')->get()
            ->reject(fn ($st) => $st->id === $user->structure_id)
            ->filter(fn ($st) => $chefs->has($st->id))
            ->each(fn ($st) => $st->setRelation('chefDestinataire', $chefs[$st->id]->first()))
            ->values();
    }
}
