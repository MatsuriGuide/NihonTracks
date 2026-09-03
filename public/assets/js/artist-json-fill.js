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

    // Coche les cases de tags dont le libellé (en minuscule) correspond à
    // l'un des noms fournis — additif, ne décoche jamais une case déjà
    // cochée par ailleurs.
    function checkTagsByName(names) {
        if (!Array.isArray(names) || names.length === 0) {
            return 0;
        }

        var lowerNames = names.map(function (n) { return String(n).trim().toLowerCase(); });
        var checkboxes = document.querySelectorAll('#artist-tags-fieldset input[type="checkbox"]');
        var matched = 0;

        checkboxes.forEach(function (cb) {
            var name = cb.getAttribute('data-tag-name');
            if (name && lowerNames.indexOf(name) !== -1) {
                cb.checked = true;
                matched++;
            }
        });

        return matched;
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

        var matchedTags = checkTagsByName(data.tags);

        var baseStatus = (filled > 0 || matchedTags > 0)
            ? (btn.getAttribute('data-success-label') || 'Champs remplis.')
            : (btn.getAttribute('data-empty-label') || 'Aucun champ reconnu dans ce JSON.');

        if (matchedTags > 0) {
            var tagsLabel = btn.getAttribute('data-tags-applied-label') || 'Tags cochés :';
            baseStatus += ' ' + tagsLabel + ' ' + matchedTags;
        }

        setStatus(baseStatus);
    });
})();
