<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Manrope', sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(15, 159, 176, 0.2), transparent 25%),
                    linear-gradient(140deg, #132238 0%, #183b56 44%, #f7f3ea 100%);
                color: #132238;
            }
            .hero-card {
                border-radius: 1.75rem;
                border: 1px solid rgba(255, 255, 255, 0.14);
                background: rgba(255, 255, 255, 0.92);
                box-shadow: 0 30px 80px rgba(19, 34, 56, 0.22);
            }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="hero-card p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-lg-7">
                        <div class="text-uppercase text-secondary small mb-2">AI Dashboard Project</div>
                        <h1 class="display-5 fw-bold mb-3">{{ config('app.name') }}</h1>
                        <p class="lead text-secondary mb-4">Pusat analisis data perusahaan, status sistem, executive summary, dan analysis API yang disiapkan untuk integrasi chatbot WhatsApp.</p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Open Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Create Account</a>
                            @endauth
                        </div>
                        <div class="row g-3">
                            @foreach ($stack as $label => $value)
                                <div class="col-12 col-md-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="small text-uppercase text-secondary">{{ str_replace('_', ' ', $label) }}</div>
                                        <div class="fw-semibold">{{ $value }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="row g-3">
                            @foreach ($modules->take(6) as $module)
                                <div class="col-12">
                                    <div class="border rounded-4 p-3">
                                        <div class="small text-uppercase text-secondary">{{ $module['navigation_group'] }}</div>
                                        <div class="fw-semibold">{{ $module['name'] }}</div>
                                        <div class="text-secondary small">{{ $module['description'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
