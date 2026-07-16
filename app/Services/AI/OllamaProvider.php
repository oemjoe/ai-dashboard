<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

class OllamaProvider extends BaseProvider
{
    public function health(AiProvider $provider): array
    {
        $health = $this->baseHealth($provider);

        if (! $provider->base_url) {
            $health['status'] = 'missing_base_url';
            $health['message'] = 'OLLAMA base URL is not configured.';

            return $health;
        }

        try {
            $response = Http::timeout($provider->timeout_seconds)->get(rtrim($provider->base_url, '/').'/api/tags');

            $health['status'] = $response->successful() ? 'ok' : 'failed';
            $health['message'] = $response->successful()
                ? 'Ollama endpoint responded successfully.'
                : 'Ollama endpoint responded with an error.';
        } catch (Throwable $exception) {
            $health['status'] = 'failed';
            $health['message'] = $this->maskingService->sanitizeFreeText($exception->getMessage()) ?? 'Ollama request failed.';
        }

        return $health;
    }
}
