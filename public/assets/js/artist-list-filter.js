(function () {
    'use strict';

    var input = document.getElementById('artist-list-search');
    var list = document.getElementById('artist-list');

    if (!input || !list) {
        return;
    }

    var items = Array.prototype.slice.call(list.querySelectorAll('.artist-list-item'));

    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();

        items.forEach(function (item) {
            var name = item.getAttribute('data-name') || '';
            item.style.display = name.indexOf(query) !== -1 ? '' : 'none';
        });
    });
})();
