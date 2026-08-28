<?php

namespace App\Controllers\Admin;

use App\Core\Auth;

class DiagnosticController extends AdminController
{
    public function __construct()
    {
        // Réservé aux admins, même si AdminController autorise déjà
        // modérateur+admin par défaut — diagnostic sensible (expose un
        // aperçu de la clé API).
        Auth::requireRole('admin');
    }

    public function openai(): void
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        $result = [
            'key_present' => $apiKey !== '',
            'key_length'  => strlen($apiKey),
            'key_prefix'  => $apiKey !== '' ? substr($apiKey, 0, 7) . '...' : null,
        ];

        if ($apiKey !== '') {
            if (function_exists('curl_init')) {
                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErrno = curl_errno($ch);
                $curlError = curl_error($ch);
                curl_close($ch);

                $result['method']           = 'curl';
                $result['http_code']        = $httpCode;
                $result['curl_errno']       = $curlErrno ?: null;
                $result['curl_error']       = $curlError ?: null;
                $result['response_snippet'] = $response !== false ? substr((string) $response, 0, 800) : null;
            } else {
                $context = stream_context_create([
                    'http' => ['header' => 'Authorization: Bearer ' . $apiKey, 'timeout' => 15, 'ignore_errors' => true],
                ]);
                $response = @file_get_contents('https://api.openai.com/v1/models', false, $context);

                $result['method']           = 'file_get_contents';
                $result['http_response_header'] = $http_response_header ?? null;
                $result['response_snippet'] = $response !== false ? substr((string) $response, 0, 800) : null;
                $result['last_error']       = error_get_last();
            }
        }

        header('Content-Type: text/plain; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Teste un vrai appel chat/completions avec le payload utilisé par la
     * suggestion de tags, pour voir la réponse brute (succès ou erreur).
     */
    public function openaiChat(): void
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        if ($apiKey === '') {
            header('Content-Type: text/plain; charset=utf-8');
            echo json_encode(['error' => 'no_key']);
            exit;
        }

        $payload = [
            'model'       => 'gpt-5-nano',
            'messages'    => [
                ['role' => 'system', 'content' => 'Tu réponds uniquement en JSON valide, sans texte autour.'],
                ['role' => 'user', 'content' => 'Réponds avec exactement : {"ok": true}'],
            ],
            'temperature' => 0.2,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        header('Content-Type: text/plain; charset=utf-8');
        echo json_encode(
            [
                'payload_sent' => $payload,
                'http_code'    => $httpCode,
                'curl_error'   => $curlError ?: null,
                'response'     => $response !== false ? json_decode($response, true) : null,
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}
