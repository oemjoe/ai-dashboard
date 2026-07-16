<?php

namespace Tests\Unit;

use App\Services\Security\MaskingService;
use Tests\TestCase;

class MaskingTest extends TestCase
{
    public function test_it_masks_secret_and_host_values(): void
    {
        $service = new MaskingService();

        $this->assertStringContainsString('****', $service->maskSecret('secret-token-value'));
        $this->assertSame('103.75.x.x', $service->maskHost('103.75.27.155'));
    }
}
