(function () {
    'use strict';

    var playBtn = document.getElementById('playlist-play-all-btn');
    var playerBox = document.getElementById('playlist-sticky-player');

    if (!playBtn || !playerBox) {
        return;
    }

    var videoIds = [];
    try {
        videoIds = JSON.parse(playBtn.getAttribute('data-video-ids') || '[]');
    } catch (e) {
        videoIds = [];
    }

    var player = null;
    var currentIndex = 0;

    function loadYouTubeApi(callback) {
        if (window.YT && window.YT.Player) {
            callback();
            return;
        }

        var previousCallback = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function () {
            if (typeof previousCallback === 'function') {
                previousCallback();
            }
            callback();
        };

        if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
            var tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(tag);
        }
    }

    function playIndex(index) {
        if (index < 0 || index >= videoIds.length || !player) {
            return;
        }

        currentIndex = index;
        player.loadVideoById(videoIds[currentIndex]);
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.ENDED) {
            playIndex(currentIndex + 1);
        }
    }

    function createPlayer() {
        playerBox.style.display = 'block';
        player = new YT.Player('playlist-player-iframe', {
            height: '215',
            width: '380',
            videoId: videoIds[0],
            playerVars: { autoplay: 1 },
            events: { onStateChange: onPlayerStateChange }
        });
    }

    playBtn.addEventListener('click', function () {
        if (videoIds.length === 0) {
            return;
        }

        if (player) {
            playerBox.style.display = 'block';
            playIndex(0);
            return;
        }

        loadYouTubeApi(createPlayer);
    });
})();
