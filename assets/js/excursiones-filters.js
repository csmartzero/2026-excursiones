(function () {
    'use strict';

    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
    function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = qs('#exc-toggle-filters');
        var panel  = qs('#exc-controls-panel');
        var select = qs('#exc-price-order');
        var search = qs('#exc-search');
        var grid   = qs('.exc-grid');

        if (!grid) return;

        // Toggle visibility
        if (toggle && panel) {
            toggle.addEventListener('click', function () {
                var open = panel.classList.toggle('is-open');
                toggle.textContent = open ? 'Ocultar filtros' : 'Mostrar filtros';
            });
        }

        // Sorting by data-price
        function sortGrid(order) {
            var cards = qsa('.exc-card', grid);
            cards.sort(function (a, b) {
                var pa = parseFloat(a.getAttribute('data-price') || '0');
                var pb = parseFloat(b.getAttribute('data-price') || '0');
                return order === 'asc' ? pa - pb : pb - pa;
            });
            // Append in order
            cards.forEach(function (c) { grid.appendChild(c); });
        }

        // Search / filter
        function filterGrid(term) {
            term = (term || '').trim().toLowerCase();
            var cards = qsa('.exc-card', grid);
            if (!term) {
                cards.forEach(function (c) { c.style.display = ''; });
                return;
            }
            cards.forEach(function (c) {
                var title = (c.getAttribute('data-title') || '').toLowerCase();
                var text = c.textContent.toLowerCase();
                var match = title.indexOf(term) !== -1 || text.indexOf(term) !== -1;
                c.style.display = match ? '' : 'none';
            });
        }

        if (select) {
            select.addEventListener('change', function () {
                var val = select.value;
                if (val === 'asc' || val === 'desc') sortGrid(val);
            });
        }

        // Debounce helper
        function debounce(fn, wait) {
            var t;
            return function () { var args = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(null, args); }, wait); };
        }

        if (search) {
            search.addEventListener('input', debounce(function (e) {
                filterGrid(e.target.value);
            }, 200));
        }
    });
})();
