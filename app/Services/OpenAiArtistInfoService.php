<?php

namespace App\Services;

class OpenAiArtistInfoService
{
    /**
     * Demande à l'IA une année de formation, un label et une courte bio pour
     * un artiste/groupe japonais. Consigne explicite de ne rien inventer :
     * un champ incertain doit revenir à null plutôt qu'une valeur devinée.
     * Le résultat est une SUGGESTION à relire, jamais enregistré directement.
     *
     * @return array{start_year: ?int, label: ?string, bio: ?string}|null
     */
    public static function suggestInfo(string $artistName): ?array
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        if ($apiKey === '' || trim($artistName) === '') {
            return null;
        }

        $prompt = "Pour l'artiste/groupe musical japonais \"{$artistName}\", donne, si tu les connais "
            . 'avec un bon niveau de confiance : l\'année de formation ou de début de carrière '
            . '(nombre entier, ou null si incertaine), le label discographique principal '
            . '(chaîne, ou null si inconnu), et une courte biographie factuelle en français '
            . '(3-4 phrases maximum, ou null si tu n\'as pas d\'information fiable). Réponds '
            . 'STRICTEMENT en JSON avec les clés start_year, label, bio, sans aucun texte autour. '
            . 'N\'invente rien : en cas de doute sur un champ, mets null pour ce champ plutôt que de deviner.';

        $payload = [
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'Tu es un assistant documentaliste musical spécialisé dans la '
                        . 'musique japonaise. Tu réponds uniquement en JSON valide. Tu ne dois '
                        . 'jamais inventer d\'information : en cas de doute, réponds null pour ce champ.',
                ],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ];

        $response = self::httpPost('https://api.openai.com/v1/chat/completions', $payload, $apiKey);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        if ($content === null) {
            return null;
        }

        $content = trim((string) preg_replace('/^```(?:json)?|```$/m', '', trim($content)));
        $parsed = json_decode($content, true);

        if (!is_array($parsed)) {
            return null;
        }

        return [
            'start_year' => isset($parsed['start_year']) && is_numeric($parsed['start_year'])
                ? (int) $parsed['start_year']
                : null,
            'label' => !empty($parsed['label']) && is_string($parsed['label'])
                ? $parsed['label']
                : null,
            'bio' => !empty($parsed['bio']) && is_string($parsed['bio'])
                ? $parsed['bio']
                : null,
        ];
    }

    private static function httpPost(string $url, array $payload, string $apiKey): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $result = curl_exec($ch);
            $hasError = curl_errno($ch) !== 0;
            curl_close($ch);

            return (!$hasError && $result !== false) ? (string) $result : null;
        }

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
                'content' => json_encode($payload),
                'timeout' => 30,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);

        return $result !== false ? $result : null;
    }
}
