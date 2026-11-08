/* Tableaux : barre de filtres (recherche, listes par colonne, dates début/fin) et « Voir plus » (20 lignes à la fois).
   S'applique à chaque tableau de la page ; <table data-no-filter> l'exclut. */
(function () {
    var PAS = 20;

    function normaliser(texte) {
        return (texte || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
    }

    function propre(texte) {
        return (texte || '').replace(/\s+/g, ' ').trim();
    }

    function creer(balise, classe, texte) {
        var el = document.createElement(balise);
        if (classe) el.className = classe;
        if (texte) el.textContent = texte;
        return el;
    }

    /** « 20/09/2026 12:39 » → « 2026-09-20 » (comparable avec la valeur d'un champ date) */
    function dateIso(texte) {
        var m = /(\d{2})\/(\d{2})\/(\d{4})/.exec(texte || '');
        return m ? m[3] + '-' + m[2] + '-' + m[1] : null;
    }

    /** Filtres disponibles : clé → [expression sur l'en-tête de colonne, libellé « tous »]. */
    var COLONNES = {
        statut: [/^statut/i, 'Tous les statuts'],
        utilisateur: [/^(utilisateur|envoy|import|cr[eé]|[eé]metteur|collaborateur|organisateur|soumis par|par$)/i, 'Tous les utilisateurs'],
        structure: [/^(structure|entreprise)/i, 'Toutes les structures'],
        type: [/^type/i, 'Tous les types'],
        action: [/^action/i, 'Toutes les actions'],
        role: [/^r[oô]le/i, 'Tous les rôles'],
        mode: [/^mode/i, 'Tous les modes'],
        transmission: [/^transmission/i, 'Toutes les transmissions'],
        lot: [/^lot/i, 'Tous les lots'],
        version: [/^version/i, 'Toutes les versions']
    };

    /** Filtres utiles pour une page : ceux que le tableau demande (data-filtres), sinon un jeu par défaut. */
    function filtresDemandes(table) {
        var voulu = table.dataset.filtres !== undefined ? table.dataset.filtres : 'statut,type,structure,date';
        return voulu.split(',').map(function (k) { return k.trim(); }).filter(Boolean);
    }

    function ameliorer(table) {
        var corps = table.tBodies[0];
        // Pas de barre dans les fenêtres modales ni dans le calendrier (FullCalendar construit ses propres tableaux)
        if (!corps || table.dataset.noFilter !== undefined || table.closest('.modal, .fc, [class*="fc-"]')) return;

        var lignes = Array.prototype.filter.call(corps.rows, function (tr) {
            // Une ligne « Aucun document » (une seule cellule étendue) n'est pas une donnée
            return !(tr.cells.length === 1 && tr.cells[0].colSpan > 1);
        });
        if (lignes.length < 1) return;

        var entetes = Array.prototype.map.call(table.tHead && table.tHead.rows[0] ? table.tHead.rows[0].cells : [], function (th) { return propre(th.textContent); });
        var indexes = lignes.map(function (tr) { return normaliser(tr.textContent); });

        var barre = creer('div', 'tbl-bar');

        // Recherche libre
        var recherche = creer('label', 'tbl-f tbl-search');
        recherche.appendChild(creer('span', '', 'Recherche'));
        var zone = creer('div', 'tbl-input');
        zone.appendChild(creer('i', 'fas fa-magnifying-glass'));
        var champ = creer('input');
        champ.type = 'search';
        champ.placeholder = 'Rechercher…';
        zone.appendChild(champ);
        recherche.appendChild(zone);
        barre.appendChild(recherche);

        // Listes déroulantes : seulement les filtres pertinents pour cette page, et seulement s'il y a un vrai choix
        var demandes = filtresDemandes(table);
        var filtres = [];
        demandes.forEach(function (cle) {
            var regle = COLONNES[cle];
            if (!regle) return;
            var col = -1;
            entetes.some(function (titre, i) { if (regle[0].test(titre)) { col = i; return true; } return false; });
            if (col < 0) return;
            var valeurs = {};
            lignes.forEach(function (tr) {
                var v = tr.cells[col] ? propre(tr.cells[col].textContent) : '';
                if (v && v !== '—' && v !== '-') valeurs[v] = true;
            });
            var liste = Object.keys(valeurs).sort();
            if (liste.length < 1 || liste.length > 25) return;

            var champSelect = creer('label', 'tbl-f');
            champSelect.appendChild(creer('span', '', entetes[col]));
            var select = creer('select');
            select.appendChild(new Option(regle[1], ''));
            liste.forEach(function (v) { select.appendChild(new Option(v, v)); });
            champSelect.appendChild(select);
            barre.appendChild(champSelect);
            filtres.push({ col: col, select: select });
        });

        // Période : sur la première colonne « Date… »
        var colDate = -1;
        entetes.some(function (titre, i) { if (/^date/i.test(titre)) { colDate = i; return true; } return false; });
        var debut = null, fin = null;
        if (demandes.indexOf('date') !== -1 && colDate >= 0) {
            [['Date début', 'debut'], ['Date fin', 'fin']].forEach(function (d) {
                var etiquette = creer('label', 'tbl-f');
                etiquette.appendChild(creer('span', '', d[0]));
                var input = creer('input');
                input.type = 'date';
                etiquette.appendChild(input);
                barre.appendChild(etiquette);
                if (d[1] === 'debut') debut = input; else fin = input;
            });
        }

        var reinit = creer('button', 'tbl-reset', 'Réinitialiser');
        reinit.type = 'button';
        reinit.hidden = true;
        barre.appendChild(reinit);
        var compte = creer('span', 'tbl-count');
        barre.appendChild(compte);

        var plus = creer('button', 'tbl-more');
        plus.type = 'button';
        var vide = creer('div', 'tbl-empty', 'Aucun résultat pour ces critères.');
        vide.hidden = true;
        var pied = creer('div', 'tbl-foot');
        pied.appendChild(plus);

        var conteneur = table.closest('.table-wrapper') || table;
        conteneur.parentNode.insertBefore(barre, conteneur);
        conteneur.parentNode.insertBefore(vide, conteneur.nextSibling);
        conteneur.parentNode.insertBefore(pied, vide.nextSibling);
        table.classList.add('tbl-js');

        var limite = PAS;

        function appliquer() {
            var q = normaliser(champ.value);
            var d0 = debut && debut.value, d1 = fin && fin.value;
            var correspondances = 0, affichees = 0;

            lignes.forEach(function (tr, i) {
                var ok = !q || indexes[i].indexOf(q) !== -1;
                for (var k = 0; ok && k < filtres.length; k++) {
                    var attendu = filtres[k].select.value;
                    if (attendu && propre(tr.cells[filtres[k].col] && tr.cells[filtres[k].col].textContent) !== attendu) ok = false;
                }
                if (ok && (d0 || d1)) {
                    var jour = dateIso(tr.cells[colDate] && tr.cells[colDate].textContent);
                    if (!jour || (d0 && jour < d0) || (d1 && jour > d1)) ok = false;
                }
                if (ok) correspondances++;
                var visible = ok && correspondances <= limite;
                tr.style.display = visible ? '' : 'none';
                tr.classList.toggle('alt', visible && (++affichees % 2 === 0));
            });

            var filtre = !!q || !!d0 || !!d1 || filtres.some(function (f) { return f.select.value; });
            reinit.hidden = !filtre;
            compte.textContent = filtre
                ? correspondances + ' résultat' + (correspondances > 1 ? 's' : '') + ' sur ' + lignes.length
                : lignes.length + ' ligne' + (lignes.length > 1 ? 's' : '');
            vide.hidden = correspondances > 0;
            var restantes = correspondances - Math.min(correspondances, limite);
            plus.hidden = restantes <= 0;
            plus.textContent = 'Voir plus (' + Math.min(PAS, restantes) + ' sur ' + restantes + ' restantes)';
        }

        function relancer() { limite = PAS; appliquer(); }
        champ.addEventListener('input', relancer);
        filtres.forEach(function (f) { f.select.addEventListener('change', relancer); });
        [debut, fin].forEach(function (d) { if (d) d.addEventListener('change', relancer); });
        reinit.addEventListener('click', function () {
            champ.value = '';
            filtres.forEach(function (f) { f.select.value = ''; });
            if (debut) debut.value = '';
            if (fin) fin.value = '';
            relancer();
        });
        plus.addEventListener('click', function () { limite += PAS; appliquer(); });

        appliquer();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.page table').forEach(ameliorer);
    });
})();
