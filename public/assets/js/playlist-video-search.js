(function () {
    'use strict';

    var input = document.getElementById('playlist-video-search');
    var results = document.getElementById('playlist-video-search-results');

    if (!input || !results) {
        return;
    }

    var searchUrl = input.getAttribute('data-search-url');
    var addUrl = input.getAttribute('data-add-url');
    var debounceTimer = null;

    function render(videos) {
        results.innerHTML = '';

        videos.forEach(function (video) {
            var li = document.createElement('li');
            li.className = 'card';

            var thumb = document.createElement('div');
            thumb.className = 'card-thumb';
            if (video.thumbnail_url) {
                thumb.style.backgroundImage = "url('" + video.thumbnail_url + "')";
            }
            li.appendChild(thumb);

            var body = document.createElement('div');
            body.className = 'card-body';

            var title = document.createElement('span');
            title.className = 'card-title';
            title.textContent = video.title || video.youtube_id;
            body.appendChild(title);

            var form = document.createElement('form');
            form.method = 'post';
            form.action = addUrl;

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'video_id';
            hidden.value = video.id;
            form.appendChild(hidden);

            var btn = document.createElement('button');
            btn.type = 'submit';
            btn.className = 'btn-small';
            btn.textContent = input.getAttribute('data-add-label') || 'Ajouter';
            form.appendChild(btn);

            body.appendChild(form);
            li.appendChild(body);
            results.appendChild(li);
        });
    }

    input.addEventListener('input', function () {
        var query = input.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            results.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetch(searchUrl + '?q=' + encodeURIComponent(query))
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    render(data.results || []);
                })
                .catch(function () {
                    // Échec silencieux : la recherche reste vide
                });
        }, 300);
    });
})();
