<?php

namespace App\Services;

use App\Models\DocumentVersion;
use App\Models\DocumentVisa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Visa d'un chef : cachet (mémorisé dans « Mes cachets ») + signature dessinée à chaque fois
 * (jamais mémorisée). Chaque visa conserve sa propre copie des images, pour qu'un changement
 * ultérieur de cachet ne modifie pas un document déjà visé.
 */
class VisaService
{
    public static function regles(User $user): array
    {
        return [
            'cachet' => [$user->cachet_chemin ? 'nullable' : 'required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'signature' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:900000'],
        ];
    }

    public static function messages(): array
    {
        return [
            'cachet.required' => 'Veuillez téléverser votre cachet.',
            'cachet.mimes' => 'Le cachet doit être une image PNG ou JPG.',
            'cachet.max' => 'Le cachet ne doit pas dépasser 2 Mo.',
            'signature.required' => 'Veuillez signer le document.',
            'signature.starts_with' => 'La signature est invalide, veuillez la refaire.',
        ];
    }

    /** Enregistre (ou remplace) le cachet mémorisé de l'utilisateur. */
    public function memoriserCachet(User $user, UploadedFile $fichier): string
    {
        $ancien = $user->cachet_chemin;
        $chemin = $fichier->store('cachets/' . $user->id, 'public');
        $user->forceFill(['cachet_chemin' => $chemin])->save();

        if ($ancien && $ancien !== $chemin) {
            Storage::disk('public')->delete($ancien);
        }

        return $chemin;
    }

    public function enregistrer(Request $request, User $user, DocumentVersion $version, string $type, ?string $destinataire = null, bool $memoriserCachet = true): DocumentVisa
    {
        if ($memoriserCachet && $request->hasFile('cachet')) {
            $this->memoriserCachet($user, $request->file('cachet'));
        }

        if (!$user->cachet_chemin || !Storage::disk('public')->exists($user->cachet_chemin)) {
            throw ValidationException::withMessages(['cachet' => 'Veuillez téléverser votre cachet.']);
        }

        $png = base64_decode(substr($request->input('signature'), strlen('data:image/png;base64,')), true);
        if (!$png || !$this->signatureNonVide($png)) {
            throw ValidationException::withMessages(['signature' => 'Veuillez signer le document.']);
        }

        $dossier = 'visas/' . $version->id . '/' . now()->format('YmdHis') . '-' . $user->id;
        $extension = pathinfo($user->cachet_chemin, PATHINFO_EXTENSION) ?: 'png';
        Storage::disk('public')->put($dossier . '-signature.png', $png);
        Storage::disk('public')->copy($user->cachet_chemin, $dossier . '-cachet.' . $extension);

        $visa = DocumentVisa::create([
            'dossier_id' => $version->dossier_id,
            'document_version_id' => $version->id,
            'user_id' => $user->id,
            'structure_id' => $user->structure_id,
            'type' => $type,
            'fonction' => $user->fonction ?: SpaceLabels::fonction($user),
            'destinataire' => $destinataire,
            'cachet_chemin' => $dossier . '-cachet.' . $extension,
            'signature_chemin' => $dossier . '-signature.png',
            'signe_le' => now(),
        ]);

        return $visa;
    }

    /** Une signature est « vide » si aucun pixel du canevas n'est opaque. */
    private function signatureNonVide(string $png): bool
    {
        $image = @imagecreatefromstring($png);
        if (!$image) {
            return false;
        }

        $w = imagesx($image);
        $h = imagesy($image);
        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                if ((($pixel = imagecolorat($image, $x, $y)) >> 24 & 0x7F) < 100) {
                    imagedestroy($image);

                    return true;
                }
            }
        }
        imagedestroy($image);

        return false;
    }
}
