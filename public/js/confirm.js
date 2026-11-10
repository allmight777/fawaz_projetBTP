/*
 * Fenêtre de confirmation commune : remplace les confirm() du navigateur.
 * Sur un <form> ou un <button> :
 *   data-confirm="Question ou phrase d'explication"
 *   data-confirm-title="Titre"                         (facultatif)
 *   data-confirm-details="Point 1|Point 2|Point 3"     (facultatif, liste à puces)
 *   data-confirm-ok="Libellé du bouton"                (facultatif)
 *   data-confirm-tone="danger|warn|accent"             (facultatif, accent par défaut)
 *   data-confirm-icon="box-archive"                    (facultatif, icône Font Awesome)
 */
(function () {
    'use strict';

    var racine = null, cible = null;
    var ICONES = { danger: 'trash', warn: 'triangle-exclamation', accent: 'circle-question' };

    function creer() {
        if (racine) return racine;
        racine = document.createElement('div');
        racine.className = 'cfm';
        racine.setAttribute('role', 'dialog');
        racine.setAttribute('aria-modal', 'true');
        racine.innerHTML =
            '<div class="cfm-box">' +
                '<div class="cfm-head"><span class="cfm-ico"><i class="fas"></i></span>' +
                '<div><h3 class="cfm-titre"></h3><p class="cfm-msg"></p></div></div>' +
                '<ul class="cfm-liste"></ul>' +
                '<div class="cfm-actions">' +
                    '<button type="button" class="cfm-non">Annuler</button>' +
                    '<button type="button" class="cfm-oui"></button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(racine);

        racine.addEventListener('click', function (e) {
            if (e.target === racine || e.target.closest('.cfm-non')) fermer();
            else if (e.target.closest('.cfm-oui')) valider();
        });
        document.addEventListener('keydown', function (e) {
            if (racine.classList.contains('open') && e.key === 'Escape') fermer();
        });
        return racine;
    }

    function ouvrir(declencheur) {
        var m = creer();
        var ton = declencheur.dataset.confirmTone || 'accent';
        var icone = declencheur.dataset.confirmIcon || ICONES[ton] || ICONES.accent;
        var details = (declencheur.dataset.confirmDetails || '').split('|').map(function (t) { return t.trim(); }).filter(Boolean);

        m.className = 'cfm cfm-' + ton;
        m.querySelector('.cfm-ico i').className = 'fas fa-' + icone;
        m.querySelector('.cfm-titre').textContent = declencheur.dataset.confirmTitle || 'Confirmer cette action ?';
        m.querySelector('.cfm-msg').textContent = declencheur.dataset.confirm || '';
        m.querySelector('.cfm-msg').style.display = declencheur.dataset.confirm ? '' : 'none';

        var liste = m.querySelector('.cfm-liste');
        liste.innerHTML = '';
        details.forEach(function (t) {
            var li = document.createElement('li');
            li.innerHTML = '<i class="fas fa-check"></i>';
            li.appendChild(document.createTextNode(' ' + t));
            liste.appendChild(li);
        });
        liste.style.display = details.length ? '' : 'none';

        m.querySelector('.cfm-oui').textContent = declencheur.dataset.confirmOk || 'Confirmer';
        cible = declencheur;
        m.classList.add('open');
        m.querySelector('.cfm-non').focus();
    }

    function fermer() {
        if (racine) racine.classList.remove('open');
        cible = null;
    }

    function valider() {
        var d = cible;
        fermer();
        if (!d) return;
        var form = d.tagName === 'FORM' ? d : d.form;
        if (form) {
            form.dataset.confirmOk = '1';
            form.submit(); // n'émet pas d'événement « submit » : pas de nouvelle confirmation
        } else if (d.href) {
            window.location.href = d.href;
        }
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]')) return;
        e.preventDefault();
        ouvrir(form);
    }, true);

    document.addEventListener('click', function (e) {
        var bouton = e.target.closest ? e.target.closest('button[data-confirm], a[data-confirm]') : null;
        if (!bouton) return;
        e.preventDefault();
        ouvrir(bouton);
    }, true);
})();
