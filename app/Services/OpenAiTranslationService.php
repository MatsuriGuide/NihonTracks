<?php

namespace App\Services;

class OpenAiTranslationService
{
    /**
     * Traduit un texte via l'API OpenAI (Chat Completions).
     * Retourne null en cas d'échec (clé absente/invalide, erreur réseau) —
     * l'appelant doit alors marquer le job de traduction comme échoué.
     */
    public static function translate(string $text, string $targetLangLabel, string $sourceLangLabel = 'French'): ?string
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        if ($apiKey === '' || trim($text) === '') {
            return null;
        }

        $payload = [
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => "You are a professional translator. Translate the given text from "
                        . "{$sourceLangLabel} to {$targetLangLabel}. Return ONLY the translated text, "
                        . 'with no explanations, no quotes, and no additional commentary.',
                ],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.3,
        ];

        $response = self::httpPost('https://api.openai.com/v1/chat/completions', $payload, $apiKey);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);
        $result = $data['choices'][0]['message']['content'] ?? null;

        return $result !== null ? trim($result) : null;
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
