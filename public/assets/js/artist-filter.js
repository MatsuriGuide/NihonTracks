(function () {
    'use strict';

    var input = document.getElementById('artist-search');
    var list = document.getElementById('artist-checklist');

    if (!input || !list) {
        return;
    }

    var items = Array.prototype.slice.call(list.querySelectorAll('.artist-option'));

    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();

        items.forEach(function (item) {
            var name = (item.getAttribute('data-name') || '').toLowerCase();
            item.style.display = name.indexOf(query) !== -1 ? '' : 'none';
        });
    });
})();
