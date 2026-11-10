{{-- Fenêtre « Partager » (informative), une seule par page ; ouverte par les boutons .btn-partager (voir partager-bouton). --}}
@php
    $structuresPartage = \App\Models\Structure::actives()->orderBy('nom')->get();
    $usersPartage = \App\Models\User::with('structure')->where('actif', true)->where('id', '!=', auth()->id())->orderBy('nom')->get();
    $iconesStructure = fn ($type) => \App\Support\Espaces::STRUCTURES[$type]['icone'] ?? ($type === 'entreprise' ? 'fa-building' : 'fa-sitemap');
@endphp
<div class="modal" id="partagerModal">
    <div class="modal-content sh-modal">
        <form method="POST" id="partagerForm" action="#">
            @csrf
            <div class="sh-sec">
                <div class="sh-label">Structures</div>
                <div class="sh-cards">
                    @foreach($structuresPartage as $structure)
                        <label class="sh-card">
                            <input type="checkbox" name="structures[]" value="{{ $structure->id }}">
                            <span class="sh-card-ico"><i class="fas {{ $iconesStructure($structure->type) }}"></i></span>
                            <span class="sh-card-nom">{{ $structure->nom }}</span>
                            <span class="sh-card-check"><i class="fas fa-check"></i></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="sh-sec">
                <div class="sh-label">Personnes</div>
                <div class="sh-searchbox"><i class="fas fa-search"></i><input type="search" id="partagerRecherche" placeholder="Rechercher une personne…" autocomplete="off"></div>
                <div class="sh-liste" id="partagerListe">
                    @foreach($usersPartage as $destinataire)
                        <label class="sh-user" data-nom="{{ mb_strtolower($destinataire->full_name . ' ' . ($destinataire->structure?->nom ?? '')) }}">
                            <input type="checkbox" name="users[]" value="{{ $destinataire->id }}">
                            <span class="sh-av">{{ mb_strtoupper(mb_substr($destinataire->prenom ?: '', 0, 1) . mb_substr($destinataire->nom ?: '', 0, 1)) }}</span>
                            <span class="sh-who"><strong>{{ $destinataire->full_name }}</strong><small>{{ $destinataire->structure?->nom ?? '—' }}</small></span>
                            <span class="sh-check"><i class="fas fa-check"></i></span>
                        </label>
                    @endforeach
                </div>
                <div class="sh-erreur-msg" id="partagerErreur">Choisissez au moins une structure ou une personne.</div>
            </div>

            <label class="md-label" for="partagerMessage">Message (facultatif)</label>
            <textarea id="partagerMessage" name="commentaire" rows="2" maxlength="2000" placeholder="Ajoutez un mot pour les destinataires…"></textarea>

            <p class="sh-note"><i class="fas fa-circle-info"></i> Les destinataires reçoivent un email, consultent le document et peuvent à leur tour le partager.</p>

            <div class="modal-actions">
                <button type="button" class="btn-modal-close" id="partagerAnnuler">Annuler</button>
                <button type="submit" class="btn-applicable"><i class="fas fa-paper-plane"></i> Partager</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('partagerModal');
    var form = document.getElementById('partagerForm');
    var erreur = document.getElementById('partagerErreur');
    function fermer() { modal.classList.remove('open'); }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.btn-partager');
        if (!b) return;
        form.action = b.dataset.action;
        form.reset();
        erreur.classList.remove('show');
        document.querySelectorAll('#partagerListe .sh-user').forEach(function (u) { u.style.display = ''; });
        modal.classList.add('open');
    });
    document.getElementById('partagerAnnuler').addEventListener('click', fermer);
    modal.addEventListener('click', function (e) { if (e.target === modal) fermer(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermer(); });
    document.getElementById('partagerRecherche').addEventListener('input', function (e) {
        var q = e.target.value.trim().toLowerCase();
        document.querySelectorAll('#partagerListe .sh-user').forEach(function (u) {
            u.style.display = !q || u.dataset.nom.indexOf(q) !== -1 ? '' : 'none';
        });
    });
    form.addEventListener('submit', function (e) {
        if (!form.querySelector('input[name="structures[]"]:checked') && !form.querySelector('input[name="users[]"]:checked')) {
            e.preventDefault();
            erreur.classList.add('show');
        }
    });
})();
</script>
