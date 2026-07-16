<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">System Health</h2>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">Connections</h3>
                @foreach ($connections as $entry)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="fw-semibold">{{ $entry['model']->name }}</div>
                        <div class="small text-soft">{{ $entry['status']['message'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="metric-card p-3 mb-4">
                <h3 class="h5 fw-bold mb-3">AI Providers</h3>
                @foreach ($providers as $provider)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="fw-semibold">{{ $provider['name'] }}</div>
                        <div class="small text-soft">{{ $provider['message'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">Recent Audit</h3>
                @foreach ($recent_audits as $audit)
                    <div class="small mb-2">{{ $audit->created_at?->format('d/m H:i') }} - {{ $audit->action }}</div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
