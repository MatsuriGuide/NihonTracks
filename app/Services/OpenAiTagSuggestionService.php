<?php

namespace App\Services;

use App\Models\Tag;

class OpenAiTagSuggestionService
{
    /**
     * Demande à l'IA de choisir, parmi la liste fixe des tags "genre"
     * existants, ceux qui correspondent le mieux à une vidéo — à partir de
     * son titre et, si connus, des artistes associés. Ne renvoie jamais un
     * tag hors de la liste fournie (l'IA doit reprendre les libellés exacts).
     *
     * @return int[] IDs des tags suggérés (0 à 3)
     */
    public static function suggestGenreTagIds(string $title, ?string $artistNames = null): array
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        if ($apiKey === '' || trim($title) === '') {
            return [];
        }

        $availableTags = Tag::genreLabelsFr();

        if (empty($availableTags)) {
            return [];
        }

        $labelList = implode(', ', array_values($availableTags));

        $prompt = "Titre de la vidéo : \"{$title}\""
            . ($artistNames ? "\nArtiste(s) : {$artistNames}" : '')
            . "\n\nParmi cette liste de genres musicaux : {$labelList}"
            . "\n\nChoisis UNIQUEMENT les genres qui correspondent le mieux à cette vidéo "
            . '(1 à 3 maximum, ou aucun si tu n\'es pas sûr). Réponds STRICTEMENT avec un '
            . 'tableau JSON de chaînes reprenant EXACTEMENT les libellés fournis ci-dessus, '
            . 'sans aucun texte autour. Exemple : ["J-Pop", "City Pop"]';

        $payload = [
            'model'       => 'gpt-5-nano',
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'Tu classes des vidéos musicales japonaises par genre. '
                        . 'Tu réponds uniquement en JSON valide, sans texte autour, et tu ne '
                        . 'choisis jamais un genre hors de la liste fournie.',
                ],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ];

        $response = self::httpPost('https://api.openai.com/v1/chat/completions', $payload, $apiKey);

        if ($response === null) {
            return [];
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        if ($content === null) {
            return [];
        }

        $content = trim((string) preg_replace('/^```(?:json)?|```$/m', '', trim($content)));
        $picked = json_decode($content, true);

        if (!is_array($picked)) {
            return [];
        }

        $labelToId = array_flip($availableTags);
        $tagIds = [];

        foreach ($picked as $label) {
            if (is_string($label) && isset($labelToId[$label])) {
                $tagIds[] = $labelToId[$label];
            }
        }

        return array_slice(array_unique($tagIds), 0, 3);
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
