<?php

namespace App\Services\AI;

use App\Models\AiProvider;

interface AiProviderInterface
{
    public function health(AiProvider $provider): array;
}
