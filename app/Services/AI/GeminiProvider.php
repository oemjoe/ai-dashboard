<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use Throwable;

class GeminiProvider extends BaseProvider
{
    public function health(AiProvider $provider): array
    {
        $health = $this->baseHealth($provider);

        if (! $health['configured']) {
            $health['status'] = 'missing_api_key';
            $health['message'] = 'No API key detected in environment.';

            return $health;
        }

        try {
            $response = $this->streamGet(
                'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode((string) $provider->model)
                .'?key='.rawurlencode((string) env($provider->api_key_env_key)),
                [
                    'Accept' => 'application/json',
                ],
                $provider->timeout_seconds
            );

            $health['status'] = $response['successful'] ? 'ok' : 'failed';
            $health['message'] = $response['successful']
                ? 'Gemini endpoint responded successfully.'
                : 'Gemini endpoint responded with an error.';
        } catch (Throwable $exception) {
            $health['status'] = 'failed';
            $health['message'] = $this->maskingService->sanitizeFreeText($exception->getMessage()) ?? 'Gemini request failed.';
        }

        return $health;
    }
}
