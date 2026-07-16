<?php

namespace Tests\Unit;

use App\Models\AiProvider;
use App\Services\AI\AiProviderManager;
use App\Services\Security\MaskingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_generates_local_summary_when_mapping_is_incomplete(): void
    {
        AiProvider::query()->create([
            'slug' => 'groq',
            'name' => 'Groq',
            'driver' => 'groq',
            'priority' => 1,
            'timeout_seconds' => 15,
            'max_retries' => 1,
            'min_confidence' => 0.75,
            'is_enabled' => true,
        ]);

        $manager = new AiProviderManager(new MaskingService());
        $summary = $manager->summarize('Accounts Receivable', [
            'mapping_status' => 'data_mapping_incomplete',
            'kpis' => [
                ['label' => 'Configured Data Sources', 'value' => 2],
            ],
        ]);

        $this->assertStringContainsString('Accounts Receivable', $summary);
        $this->assertStringContainsString('Data mapping', $summary);
    }
}
