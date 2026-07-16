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
        <style>
            body {
                font-family: 'Manrope', sans-serif;
                min-height: 100vh;
                background:
                    radial-gradient(circle at top left, rgba(15, 159, 176, 0.18), transparent 25%),
                    linear-gradient(135deg, #132238 0%, #183b56 45%, #eef4f7 100%);
            }
            .guest-card {
                border: 0;
                border-radius: 1.5rem;
                box-shadow: 0 24px 60px rgba(19, 34, 56, 0.2);
            }
        </style>
    </head>
    <body class="d-flex align-items-center justify-content-center p-3">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="text-center text-white mb-4">
                        <div class="text-uppercase small opacity-75">Tirta AI Dashboard</div>
                        <h1 class="h3 fw-bold">{{ config('app.name') }}</h1>
                    </div>
                    <div class="card guest-card">
                        <div class="card-body p-4 p-lg-5">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
