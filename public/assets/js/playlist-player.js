(function () {
    'use strict';

    var panel = document.getElementById('playlist-player-panel');
    if (!panel) {
        return;
    }

    var trackEls = Array.prototype.slice.call(document.querySelectorAll('#playlist-tracklist .track'));
    var queue = trackEls.map(function (el) {
        var titleEl = el.querySelector('.track-title');
        return {
            id: el.getAttribute('data-youtube-id'),
            title: titleEl ? titleEl.textContent.trim() : ''
        };
    });

    if (queue.length === 0) {
        return;
    }

    var currentIndex = 0;
    var player = null;
    var nowPlayingLabel = document.getElementById('now-playing-label');

    function setActiveTrack(index) {
        trackEls.forEach(function (el, i) {
            el.classList.toggle('is-playing', i === index);
        });
        if (nowPlayingLabel && queue[index]) {
            nowPlayingLabel.textContent = queue[index].title;
        }
    }

    function playIndex(index) {
        if (index < 0 || index >= queue.length || !player || !player.loadVideoById) {
            return;
        }
        currentIndex = index;
        setActiveTrack(currentIndex);
        player.loadVideoById(queue[currentIndex].id);
    }

    window.onYouTubeIframeAPIReady = function () {
        player = new YT.Player('playlist-player', {
            height: '360',
            width: '100%',
            videoId: queue[0].id,
            playerVars: { rel: 0 },
            events: {
                onReady: function () {
                    setActiveTrack(0);
                },
                onStateChange: function (event) {
                    if (event.data === YT.PlayerState.ENDED) {
                        playIndex(currentIndex + 1);
                    }
                }
            }
        });
    };

    trackEls.forEach(function (el, i) {
        var btn = el.querySelector('.track-play');
        if (btn) {
            btn.addEventListener('click', function () {
                playIndex(i);
            });
        }
    });

    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.body.appendChild(tag);
})();
