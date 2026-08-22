(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.playlist-toggle-btn');

        if (!btn) {
            return;
        }

        var url = btn.getAttribute('data-toggle-url');

        if (!url || btn.disabled) {
            return;
        }

        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                btn.classList.toggle('is-added', !!data.added);
            })
            .catch(function () {
                // En cas d'échec réseau, on laisse l'état affiché tel quel
            })
            .finally(function () {
                btn.disabled = false;
            });
    });
})();
