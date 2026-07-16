<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use App\Models\Company;
use App\Models\DashboardModule;
use App\Models\HelpArticle;
use App\Models\SourceRepository;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['code' => 'TNP', 'name' => 'Tirta Nusa Persada', 'category' => 'holding', 'region' => 'Indonesia'],
            ['code' => 'TMS', 'name' => 'Tunas Mitra Sejati', 'category' => 'subsidiary', 'region' => 'Indonesia'],
            ['code' => 'DPS', 'name' => 'Tirta Terminal', 'category' => 'subsidiary', 'region' => 'Indonesia'],
            ['code' => 'FMS', 'name' => 'Flotia FMS', 'category' => 'system', 'region' => 'Indonesia'],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(
                ['code' => $company['code']],
                $company + ['is_active' => true],
            );
        }

        foreach (config('ai_dashboard.permissions', []) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (config('ai_dashboard.roles', []) as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $permissions = config("ai_dashboard.role_permissions.{$roleName}", []);

            if ($permissions === ['*']) {
                $role->syncPermissions(Permission::all());
                continue;
            }

            $role->syncPermissions($permissions);
        }

        foreach (config('ai_dashboard.modules', []) as $module) {
            DashboardModule::query()->updateOrCreate(
                ['slug' => $module['slug']],
                [
                    'name' => $module['name'],
                    'navigation_group' => $module['navigation_group'],
                    'description' => $module['description'],
                    'permission_name' => $module['permission'],
                    'guide_slug' => $module['guide_slug'],
                    'display_order' => $module['display_order'],
                    'mapping_status' => 'data_mapping_incomplete',
                    'is_enabled' => true,
                ],
            );
        }

        $providers = [
            [
                'slug' => 'groq',
                'name' => 'Groq',
                'driver' => 'groq',
                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                'api_key_env_key' => 'GROQ_API_KEY',
                'priority' => 1,
                'is_enabled' => true,
            ],
            [
                'slug' => 'gemini',
                'name' => 'Google Gemini',
                'driver' => 'gemini',
                'model' => env('GEMINI_MODEL', 'gemini-2.5-pro'),
                'api_key_env_key' => 'GEMINI_API_KEY',
                'priority' => 2,
                'is_enabled' => true,
            ],
            [
                'slug' => 'ollama',
                'name' => 'Ollama',
                'driver' => 'ollama',
                'model' => env('OLLAMA_MODEL', 'llama3.1'),
                'base_url' => env('OLLAMA_BASE_URL', 'http://ollama:11434'),
                'priority' => 3,
                'is_enabled' => false,
            ],
        ];

        foreach ($providers as $provider) {
            AiProvider::query()->updateOrCreate(
                ['slug' => $provider['slug']],
                $provider + [
                    'timeout_seconds' => 15,
                    'max_retries' => 1,
                    'min_confidence' => 0.75,
                ],
            );
        }

        $helpArticles = [
            ['slug' => 'getting-started', 'title' => 'Getting Started', 'section' => 'Help', 'markdown_path' => 'docs/user-guide/getting-started.md', 'route_path' => '/help/getting-started', 'sort_order' => 10, 'is_public' => false],
            ['slug' => 'dashboard-overview', 'title' => 'Dashboard Overview', 'section' => 'Help', 'markdown_path' => 'docs/user-guide/dashboard-overview.md', 'route_path' => '/help/dashboard-overview', 'sort_order' => 20, 'is_public' => false],
            ['slug' => 'administration', 'title' => 'Administration', 'section' => 'Help', 'markdown_path' => 'docs/user-guide/administration.md', 'route_path' => '/help/administration', 'sort_order' => 30, 'is_public' => false],
        ];

        foreach ($helpArticles as $article) {
            HelpArticle::query()->updateOrCreate(['slug' => $article['slug']], $article);
        }

        SourceRepository::query()->updateOrCreate(
            ['path' => base_path()],
            [
                'name' => 'ai-dashboard',
                'branch' => env('APP_DEFAULT_BRANCH', 'main'),
                'provider' => 'filesystem',
                'is_active' => true,
            ],
        );

        $initialCompany = Company::query()->where('code', env('INITIAL_ADMIN_COMPANY', 'TNP'))->first();

        $user = User::query()->updateOrCreate(
            ['email' => env('INITIAL_ADMIN_EMAIL', 'admin@tirtanusa.com')],
            [
                'company_id' => $initialCompany?->id,
                'name' => env('INITIAL_ADMIN_NAME', 'Initial Super Admin'),
                'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'ChangeMeImmediately123!')),
                'preferred_company_code' => $initialCompany?->code,
                'is_forced_password_reset' => true,
            ],
        );

        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
    }
}
