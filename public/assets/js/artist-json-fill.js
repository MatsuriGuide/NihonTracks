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

    function setStatus(html) {
        if (statusEl) {
            statusEl.innerHTML = html;
        }
    }

    // Coche les cases de tags dont le libellé (normalisé) correspond à l'un
    // des noms fournis — additif, ne décoche jamais une case déjà cochée.
    // Normalisation : minuscule + espaces multiples réduits à un seul, pour
    // absorber les petites variations de saisie (espace en trop, etc.).
    function normalize(str) {
        return String(str).trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function checkTagsByName(names) {
        if (!Array.isArray(names) || names.length === 0) {
            return { matched: 0, unmatched: [] };
        }

        var normalizedNames = names.map(normalize);
        var checkboxes = document.querySelectorAll('#artist-tags-fieldset input[type="checkbox"]');
        var matchedNames = [];

        checkboxes.forEach(function (cb) {
            var name = cb.getAttribute('data-tag-name');
            if (name && normalizedNames.indexOf(normalize(name)) !== -1) {
                cb.checked = true;
                matchedNames.push(normalize(name));
            }
        });

        var unmatched = names.filter(function (n) {
            return matchedNames.indexOf(normalize(n)) === -1;
        });

        return { matched: matchedNames.length, unmatched: unmatched };
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

        var tagResult = checkTagsByName(data.tags);

        var baseStatus = (filled > 0 || tagResult.matched > 0)
            ? (btn.getAttribute('data-success-label') || 'Champs remplis.')
            : (btn.getAttribute('data-empty-label') || 'Aucun champ reconnu dans ce JSON.');

        if (tagResult.matched > 0) {
            var tagsLabel = btn.getAttribute('data-tags-applied-label') || 'Tags cochés :';
            baseStatus += ' ' + tagsLabel + ' ' + tagResult.matched;
        }

        if (tagResult.unmatched.length > 0) {
            baseStatus += ' — <strong>' + (btn.getAttribute('data-tags-unmatched-label') || 'Tags non trouvés :')
                + '</strong> ' + tagResult.unmatched.join(', ');
        }

        setStatus(baseStatus);
    });
})();
