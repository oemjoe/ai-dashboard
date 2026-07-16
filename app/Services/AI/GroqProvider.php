<?php

namespace App\Services\AI;

use App\Models\AiProvider;

class GroqProvider extends BaseProvider
{
    public function health(AiProvider $provider): array
    {
        $health = $this->baseHealth($provider);
        $health['status'] = $health['configured'] ? 'configured' : 'missing_api_key';
        $health['message'] = $health['configured']
            ? 'API key detected. Live inference test is intentionally skipped until production secrets are approved.'
            : 'No API key detected in environment.';

        return $health;
    }
}
