(function () {
    'use strict';

    var btn = document.getElementById('suggest-artist-info-btn');

    if (!btn) {
        return;
    }

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
        var originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = btn.getAttribute('data-loading-label') || '...';

        fetch(btn.getAttribute('data-url'), { method: 'POST' })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.error) {
                    return;
                }

                var yearField = document.getElementById('start_year');
                var labelField = document.getElementById('label');
                var bioField = document.getElementById('bio');

                if (yearField && data.start_year && !yearField.value) {
                    yearField.value = data.start_year;
                }
                if (labelField && data.label && !labelField.value) {
                    labelField.value = data.label;
                }
                if (bioField && data.bio && !bioField.value) {
                    bioField.value = data.bio;
                }

                checkTagsByName(data.tags);
            })
            .catch(function () {
                // Échec silencieux : les champs restent à compléter manuellement
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });
})();
