<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HealthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_health_endpoint_returns_ok_payload(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_chatbot_service_can_access_internal_health_endpoint(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Chatbot Service');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/health/internal')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_viewer_cannot_access_internal_health_endpoint(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Viewer');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/health/internal')
            ->assertForbidden();
    }
}
