<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">AI Providers</h2>
            <div class="text-soft">Groq, Gemini, dan Ollama dengan health overview dan fallback strategy.</div>
        </div>
    </x-slot>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.ai-providers.create') }}" class="btn btn-primary">Add Provider</a>
    </div>

    <div class="row g-3">
        @foreach ($providers as $provider)
            @php($providerHealth = $health[$provider->slug] ?? null)
            <div class="col-12 col-lg-6">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">{{ $provider->name }}</div>
                            <div class="text-soft small">{{ strtoupper($provider->driver) }} | {{ $provider->model ?: 'Model pending' }}</div>
                        </div>
                        <span class="badge text-bg-{{ ($providerHealth['status'] ?? null) === 'ok' ? 'success' : (($providerHealth['configured'] ?? false) ? 'warning' : 'secondary') }}">
                            {{ $providerHealth['status'] ?? 'unknown' }}
                        </span>
                    </div>
                    <div class="text-soft small mt-2">{{ $providerHealth['message'] ?? 'No health information yet.' }}</div>
                    <div class="d-flex gap-2 mt-3">
                        <form method="POST" action="{{ route('admin.ai-providers.test', $provider) }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm">Health Test</button>
                        </form>
                        <a href="{{ route('admin.ai-providers.edit', $provider) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
