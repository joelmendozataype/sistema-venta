<?php

namespace Tests\Selenium;

use RuntimeException;

/**
 * Cliente ligero W3C WebDriver nativo en PHP (basado en cURL).
 * Permite ejecutar pruebas automatizadas en navegador (ChromeDriver / Selenium Grid 4)
 * directamente con PHP sin depender de paquetes externos si Composer no está disponible.
 */
class W3CWebDriverClient
{
    private string $serverUrl;
    private ?string $sessionId = null;

    public function __construct(string $serverUrl = 'http://localhost:4444')
    {
        $this->serverUrl = rtrim($serverUrl, '/');
    }

    public function startSession(bool $headless = true): string
    {
        $args = ['--window-size=1920,1080', '--disable-gpu', '--no-sandbox'];
        if ($headless) {
            $args[] = '--headless=new';
        }

        $payload = [
            'capabilities' => [
                'alwaysMatch' => [
                    'browserName' => 'chrome',
                    'goog:chromeOptions' => [
                        'args' => $args,
                    ],
                ],
            ],
        ];

        $response = $this->request('POST', '/session', $payload);
        $this->sessionId = $response['value']['sessionId'] ?? $response['sessionId'] ?? null;

        if (!$this->sessionId) {
            throw new RuntimeException('No se pudo iniciar la sesión W3C WebDriver.');
        }

        return $this->sessionId;
    }

    public function navigateTo(string $url): void
    {
        $this->request('POST', "/session/{$this->sessionId}/url", ['url' => $url]);
    }

    public function getCurrentUrl(): string
    {
        $res = $this->request('GET', "/session/{$this->sessionId}/url");
        return (string) ($res['value'] ?? '');
    }

    public function findElementByCss(string $selector): string
    {
        $res = $this->request('POST', "/session/{$this->sessionId}/element", [
            'using' => 'css selector',
            'value' => $selector,
        ]);
        $val = $res['value'] ?? [];
        return (string) (reset($val) ?: '');
    }

    public function sendKeys(string $elementId, string $text): void
    {
        $this->request('POST', "/session/{$this->sessionId}/element/{$elementId}/value", [
            'text' => $text,
            'value' => preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY),
        ]);
    }

    public function click(string $elementId): void
    {
        $this->request('POST', "/session/{$this->sessionId}/element/{$elementId}/click", new \stdClass());
    }

    public function getElementText(string $elementId): string
    {
        $res = $this->request('GET', "/session/{$this->sessionId}/element/{$elementId}/text");
        return trim((string) ($res['value'] ?? ''));
    }

    public function quit(): void
    {
        if ($this->sessionId !== null) {
            try {
                $this->request('DELETE', "/session/{$this->sessionId}");
            } catch (\Throwable $e) {
                // Ignorar errores al cerrar sesión
            }
            $this->sessionId = null;
        }
    }

    private function request(string $method, string $endpoint, $body = null): array
    {
        $ch = curl_init($this->serverUrl . $endpoint);
        $headers = ['Content-Type: application/json; charset=utf-8'];
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code >= 400) {
            throw new RuntimeException("Error WebDriver HTTP ($code) en $endpoint: " . ($raw ?: 'sin respuesta'));
        }

        return json_decode($raw, true) ?? [];
    }
}

