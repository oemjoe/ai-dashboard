<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectionManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_connection_manager_page(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@tirtanusa.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/administration/data-connections')
            ->assertOk()
            ->assertSee('Data Connections');
    }

    public function test_viewer_cannot_view_connection_manager_page(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Viewer');

        $this->actingAs($user)
            ->get('/administration/data-connections')
            ->assertForbidden();
    }
}
