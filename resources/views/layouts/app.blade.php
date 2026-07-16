@php
    $moduleGroups = collect(config('ai_dashboard.modules'))->groupBy('navigation_group');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Manrope', sans-serif;
                background:
                    radial-gradient(circle at top right, rgba(15, 159, 176, 0.18), transparent 24%),
                    linear-gradient(180deg, #f7f3ea 0%, #eef4f7 58%, #ffffff 100%);
                color: #132238;
            }
            .sidebar-panel {
                min-height: 100vh;
                background: linear-gradient(180deg, rgba(19, 34, 56, 0.98), rgba(24, 59, 86, 0.96));
                color: #fff;
            }
            .sidebar-panel .nav-link {
                color: rgba(255, 255, 255, 0.8);
                border-radius: 0.9rem;
                margin-bottom: 0.25rem;
            }
            .sidebar-panel .nav-link:hover,
            .sidebar-panel .nav-link.active {
                color: #fff;
                background: rgba(255, 255, 255, 0.1);
            }
            .page-card {
                background: rgba(255, 255, 255, 0.88);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(19, 34, 56, 0.08);
                border-radius: 1.25rem;
                box-shadow: 0 18px 50px rgba(19, 34, 56, 0.08);
            }
            .metric-card {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(238, 244, 247, 0.92));
                border: 1px solid rgba(24, 59, 86, 0.08);
                border-radius: 1rem;
            }
            .text-soft {
                color: #66778b;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid px-0">
            <div class="row g-0">
                <aside class="col-12 col-lg-3 col-xl-2 sidebar-panel p-4">
                    <div class="mb-4">
                        <div class="small text-uppercase text-white-50 mb-2">Tirta AI</div>
                        <h1 class="h4 fw-bold mb-1">{{ config('app.name') }}</h1>
                        <div class="small text-white-50">Data and system analysis center</div>
                    </div>

                    <nav class="nav flex-column mb-4">
                        @can('dashboard.view')
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-grid-1x2 me-2"></i>Dashboard
                            </a>
                        @endcan

                        @can('help.view')
                            <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                                <i class="bi bi-journal-richtext me-2"></i>Manual Book
                            </a>
                        @endcan

                        @can('admin.system-health.view')
                            <a class="nav-link {{ request()->routeIs('admin.system-health.*') ? 'active' : '' }}" href="{{ route('admin.system-health.index') }}">
                                <i class="bi bi-heart-pulse me-2"></i>System Health
                            </a>
                        @endcan

                        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-gear me-2"></i>Profile
                        </a>
                    </nav>

                    @if (auth()->user()->can('admin.users.view') || auth()->user()->can('admin.roles.view') || auth()->user()->can('admin.connections.manage') || auth()->user()->can('admin.ai-providers.manage'))
                        <div class="small text-uppercase text-white-50 mt-4 mb-2">Administration</div>
                        <nav class="nav flex-column">
                            @can('admin.users.view')
                                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    User Management
                                </a>
                            @endcan
                            @can('admin.roles.view')
                                <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                    Roles & Permissions
                                </a>
                            @endcan
                            @can('admin.connections.manage')
                                <a class="nav-link {{ request()->routeIs('admin.data-connections.*') ? 'active' : '' }}" href="{{ route('admin.data-connections.index') }}">
                                    Data Connections
                                </a>
                            @endcan
                            @can('admin.ai-providers.manage')
                                <a class="nav-link {{ request()->routeIs('admin.ai-providers.*') ? 'active' : '' }}" href="{{ route('admin.ai-providers.index') }}">
                                    AI Providers
                                </a>
                            @endcan
                        </nav>
                    @endif

                    @foreach ($moduleGroups as $group => $modules)
                        <div class="small text-uppercase text-white-50 mt-4 mb-2">{{ $group }}</div>
                        <nav class="nav flex-column">
                            @foreach ($modules as $module)
                                @can($module['permission'])
                                    <a class="nav-link {{ request()->routeIs('modules.show') && request()->route('module') === $module['slug'] ? 'active' : '' }}"
                                        href="{{ route('modules.show', $module['slug']) }}">
                                        {{ $module['name'] }}
                                    </a>
                                @endcan
                            @endforeach
                        </nav>
                    @endforeach

                    <div class="mt-4 pt-4 border-top border-white border-opacity-10">
                        <div class="small text-white-50">Signed in as</div>
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-white-50 mb-3">{{ auth()->user()->preferred_company_code ?: 'No company scope' }}</div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm w-100">Logout</button>
                        </form>
                    </div>
                </aside>

                <div class="col-12 col-lg-9 col-xl-10">
                    <div class="p-3 p-lg-4">
                        <div class="page-card p-3 p-lg-4">
                            @isset($header)
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                    <div>{{ $header }}</div>
                                    <div class="text-soft small">Timezone: {{ config('app.timezone') }} | Updated: {{ now()->format('d M Y H:i') }}</div>
                                </div>
                            @endisset

                            @if (session('status'))
                                <div class="alert alert-info">{{ session('status') }}</div>
                            @endif

                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
