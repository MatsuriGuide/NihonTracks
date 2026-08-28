(function () {
    'use strict';

    var btn = document.getElementById('suggest-tags-btn');

    if (!btn) {
        return;
    }

    btn.addEventListener('click', function () {
        var titleField = document.getElementById('title');
        var title = titleField ? titleField.value.trim() : '';

        if (!title) {
            return;
        }

        var artistNames = Array.prototype.slice
            .call(document.querySelectorAll('#artist-checklist input[type="checkbox"]:checked'))
            .map(function (cb) {
                var label = cb.closest('label');
                return label ? label.textContent.trim() : '';
            })
            .filter(Boolean)
            .join(', ');

        var originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = btn.getAttribute('data-loading-label') || '...';

        var formData = new FormData();
        formData.append('title', title);
        formData.append('artist_names', artistNames);

        fetch(btn.getAttribute('data-url'), {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                (data.tag_ids || []).forEach(function (id) {
                    var checkbox = document.querySelector('input[name="tag_ids[]"][value="' + id + '"]');
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            })
            .catch(function () {
                // Échec silencieux : les tags restent à cocher manuellement
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });
})();
