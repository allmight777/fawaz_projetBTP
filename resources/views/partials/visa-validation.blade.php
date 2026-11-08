{{-- Cachet + signature du validateur, affichés seulement quand la décision « valide » est choisie. --}}
<div id="visaValidation" hidden>
    <p style="color:var(--muted); font-size: var(--fs-m); margin-bottom:4px;">Pour valider, apposez votre cachet et votre signature : ils figureront sur le document validé, avec le visa de la structure émettrice et le tampon de validation.</p>
    @include('partials.visa-fields')
</div>
<script>
    (function () {
        var zone = document.getElementById('visaValidation');
        var champs = document.querySelectorAll('[name=decision]');
        function maj() {
            var choix = Array.prototype.filter.call(champs, function (c) { return c.type === 'radio' ? c.checked : true; })[0];
            var actif = choix && choix.value === 'valide';
            zone.hidden = !actif;
            // Un champ désactivé n'est ni envoyé ni contrôlé par le navigateur
            zone.querySelectorAll('input').forEach(function (i) {
                if (i.closest('.visa-upload[hidden]')) { i.disabled = true; return; }
                i.disabled = !actif;
            });
        }
        champs.forEach(function (c) { c.addEventListener('change', maj); });
        maj();
    })();
</script>
