(function () {
    'use strict';

    var btn = document.getElementById('json-fill-btn');

    if (!btn) {
        return;
    }

    var statusEl = document.getElementById('json-fill-status');
    var fieldMap = {
        start_year: 'start_year',
        end_year: 'end_year',
        label: 'label',
        bio: 'bio'
    };

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    btn.addEventListener('click', function () {
        var input = document.getElementById('json_fill_input');
        var raw = input ? input.value.trim() : '';

        if (!raw) {
            return;
        }

        var data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            setStatus(btn.getAttribute('data-invalid-label') || 'JSON invalide.');
            return;
        }

        var filled = 0;

        Object.keys(fieldMap).forEach(function (key) {
            if (data[key] === undefined || data[key] === null || data[key] === '') {
                return;
            }
            var field = document.getElementById(fieldMap[key]);
            if (field) {
                field.value = data[key];
                filled++;
            }
        });

        setStatus(
            filled > 0
                ? (btn.getAttribute('data-success-label') || 'Champs remplis.')
                : (btn.getAttribute('data-empty-label') || 'Aucun champ reconnu dans ce JSON.')
        );
    });
})();
