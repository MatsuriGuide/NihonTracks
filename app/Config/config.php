<?php

return [
    'app_name'        => 'NihonTracks',
    'app_url'         => $_ENV['APP_URL'] ?? 'http://localhost',
    'default_lang'    => $_ENV['APP_DEFAULT_LANG'] ?? 'fr',
    'langs'           => explode(',', $_ENV['APP_LANGS'] ?? 'fr,en,ja'),
    'youtube_api_key' => $_ENV['YOUTUBE_API_KEY'] ?? '',
    'openai_api_key'  => $_ENV['OPENAI_API_KEY'] ?? '',
];
