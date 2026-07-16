<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use App\Services\Security\MaskingService;

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
}
