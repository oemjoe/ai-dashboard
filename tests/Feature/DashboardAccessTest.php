<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_super_admin_can_open_dashboard(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@tirtanusa.com')->firstOrFail();
        $user->assignRole(Role::findByName('Super Admin'));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Overview');
    }
}
