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

        var baseStatus = filled > 0
            ? (btn.getAttribute('data-success-label') || 'Champs remplis.')
            : (btn.getAttribute('data-empty-label') || 'Aucun champ reconnu dans ce JSON.');

        // Les tags (noms textuels) sont résolus et ajoutés côté serveur —
        // fusionnés avec ceux déjà présents, jamais un remplacement.
        var tagsUrl = btn.getAttribute('data-tags-url');
        if (tagsUrl && Array.isArray(data.tags) && data.tags.length > 0) {
            setStatus(baseStatus);

            var formData = new FormData();
            data.tags.forEach(function (name) {
                formData.append('tag_names[]', name);
            });

            fetch(tagsUrl, { method: 'POST', body: formData })
                .then(function (response) { return response.json(); })
                .then(function (result) {
                    if (result.applied_tag_ids && result.applied_tag_ids.length > 0) {
                        var tagsLabel = btn.getAttribute('data-tags-applied-label') || 'Tags ajoutés :';
                        setStatus(baseStatus + ' ' + tagsLabel + ' ' + result.applied_tag_ids.length);
                    }
                })
                .catch(function () {
                    // Échec silencieux sur la partie tags — les autres champs restent remplis
                });
        } else {
            setStatus(baseStatus);
        }
    });
})();
