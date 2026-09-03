(function () {
    'use strict';

    var btn = document.getElementById('suggest-artist-info-btn');

    if (!btn) {
        return;
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

                if (data.applied_tag_names && data.applied_tag_names.length > 0) {
                    btn.insertAdjacentHTML(
                        'afterend',
                        '<span style="margin-left:0.5rem;">' + data.applied_tag_names.join(', ') + '</span>'
                    );
                }
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
