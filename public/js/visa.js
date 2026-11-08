/* Cachet + signature dessinée : chaque bloc [data-visa] est autonome. */
(function () {
    function init(box) {
        var canvas = box.querySelector('[data-visa-pad]');
        var hidden = box.querySelector('input[name="signature"]');
        var form = box.closest('form');
        if (!canvas || !hidden || !form || box.dataset.ready) return;
        box.dataset.ready = '1';

        var ctx = canvas.getContext('2d');
        var dessine = false, vide = true, dernier = null;

        function ajuster() {
            // Le canevas est dans une modale cachée à l'init : on le dimensionne au premier tracé.
            var r = canvas.getBoundingClientRect();
            if (!r.width) return;
            var d = window.devicePixelRatio || 1;
            if (vide && (canvas.width !== Math.round(r.width * d) || canvas.height !== Math.round(r.height * d))) {
                canvas.width = Math.round(r.width * d);
                canvas.height = Math.round(r.height * d);
                ctx.scale(d, d);
            }
            ctx.lineWidth = 2.4; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#0b1b4d';
        }
        function point(e) {
            var r = canvas.getBoundingClientRect();
            return { x: e.clientX - r.left, y: e.clientY - r.top };
        }
        canvas.addEventListener('pointerdown', function (e) {
            ajuster();
            dessine = true; dernier = point(e);
            canvas.setPointerCapture(e.pointerId);
            ctx.beginPath(); ctx.arc(dernier.x, dernier.y, 1.2, 0, 7); ctx.fill();
            e.preventDefault();
        });
        canvas.addEventListener('pointermove', function (e) {
            if (!dessine) return;
            var p = point(e);
            ctx.beginPath(); ctx.moveTo(dernier.x, dernier.y); ctx.lineTo(p.x, p.y); ctx.stroke();
            dernier = p; vide = false;
            box.classList.add('signed');
            e.preventDefault();
        });
        ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) {
            canvas.addEventListener(ev, function () { dessine = false; });
        });

        var effacer = box.querySelector('[data-visa-clear]');
        if (effacer) effacer.addEventListener('click', function () {
            ctx.save(); ctx.setTransform(1, 0, 0, 1, 0, 0); ctx.clearRect(0, 0, canvas.width, canvas.height); ctx.restore();
            vide = true; hidden.value = ''; box.classList.remove('signed');
        });

        var changer = box.querySelector('[data-visa-change]');
        if (changer) changer.addEventListener('click', function () {
            var zone = box.querySelector('.visa-upload');
            var champ = zone.querySelector('input[type=file]');
            zone.hidden = false; champ.disabled = false; champ.required = true;
            changer.hidden = true;
        });

        // Remplit le champ caché ; renvoie false (et signale l'erreur) si la signature manque.
        box.__prepare = function () {
            if (box.offsetParent === null) return true; // bloc masqué : pas de visa demandé
            if (vide) {
                box.classList.add('error');
                canvas.scrollIntoView({ block: 'center', behavior: 'smooth' });
                return false;
            }
            hidden.value = canvas.toDataURL('image/png');
            return true;
        };
        form.addEventListener('submit', function (e) {
            if (!box.__prepare()) e.preventDefault();
        });
    }
    // Pour les formulaires qui soumettent eux-mêmes via form.submit() (qui ne déclenche pas « submit »)
    window.visaPrepare = function (form) {
        return Array.prototype.every.call(form.querySelectorAll('[data-visa]'), function (b) { return !b.__prepare || b.__prepare(); });
    };
    function tout() { document.querySelectorAll('[data-visa]').forEach(init); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', tout); else tout();
})();
