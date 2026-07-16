<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use App\Services\Security\MaskingService;
use RuntimeException;

abstract class BaseProvider implements AiProviderInterface
{
    public function __construct(protected readonly MaskingService $maskingService)
    {
    }

    protected function baseHealth(AiProvider $provider): array
    {
        $configured = $provider->api_key_env_key
            ? filled(env($provider->api_key_env_key))
            : filled($provider->base_url);

        return [
            'slug' => $provider->slug,
            'name' => $provider->name,
            'driver' => $provider->driver,
            'model' => $provider->model,
            'configured' => $configured,
            'enabled' => $provider->is_enabled,
            'last_success_at' => optional($provider->last_success_at)?->toIso8601String(),
            'last_error_at' => optional($provider->last_error_at)?->toIso8601String(),
            'last_error_message' => $provider->last_error_message,
        ];
    }

    protected function streamGet(string $url, array $headers = [], int $timeout = 15): array
    {
        $warning = null;
        $headerLines = [];

        foreach ($headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }

        set_error_handler(function (int $severity, string $message) use (&$warning): bool {
            $warning = $message;

            return true;
        });

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => $timeout,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headerLines),
                ],
            ]);

            $body = file_get_contents($url, false, $context);
            $responseHeaders = $http_response_header ?? [];
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            throw new RuntimeException($warning ?: 'HTTP stream request failed.');
        }

        $statusCode = 0;

        if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)) {
            $statusCode = (int) $matches[1];
        }

        return [
            'successful' => $statusCode >= 200 && $statusCode < 300,
            'status' => $statusCode,
            'body' => $body,
        ];
    }
}
