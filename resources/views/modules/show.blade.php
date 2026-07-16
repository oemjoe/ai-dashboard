<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">{{ $module->navigation_group }}</div>
            <h2 class="h3 fw-bold mb-1">{{ $module->name }}</h2>
            <div class="text-soft">{{ $module->description }}</div>
        </div>
    </x-slot>

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <span class="badge text-bg-warning">{{ str_replace('_', ' ', $mapping_status) }}</span>
            <span class="text-soft small ms-2">Data as of: {{ optional($data_as_of)->format('d M Y H:i') ?: 'Belum tersedia' }}</span>
        </div>
        <a href="{{ route('help.show', $module->guide_slug ?: 'getting-started') }}" class="btn btn-outline-secondary btn-sm">Panduan Modul</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($kpis as $kpi)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card p-3 h-100">
                    <div class="text-soft small text-uppercase">{{ $kpi['label'] }}</div>
                    <div class="h3 fw-bold mb-0">{{ $kpi['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="metric-card p-3 mb-4">
                <h3 class="h5 fw-bold mb-3">Findings</h3>
                @foreach ($findings as $finding)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold">{{ $finding['title'] }}</div>
                            <span class="badge text-bg-{{ $finding['severity'] === 'warning' ? 'warning' : 'info' }}">{{ $finding['severity'] }}</span>
                        </div>
                        <div class="text-soft small mt-2">{{ $finding['description'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">Filter Context</h3>
                <div class="row g-3">
                    @forelse ($filters as $key => $value)
                        <div class="col-12 col-md-6">
                            <div class="border rounded-4 p-3">
                                <div class="text-soft small text-uppercase">{{ $key }}</div>
                                <div class="fw-semibold">{{ $value }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-soft">Belum ada filter aktif. Gunakan query string saat integrasi API dan chatbot dimulai.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="metric-card p-3 mb-4">
                <h3 class="h5 fw-bold mb-3">AI Summary</h3>
                <div class="text-soft">{{ $ai_summary }}</div>
            </div>

            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">Mapped Connections</h3>
                @forelse ($connections as $connection)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="fw-semibold">{{ $connection->name }}</div>
                        <div class="small text-soft">{{ $connection->host_masked ?: $connection->host ?: 'Host pending' }} | {{ $connection->database_name }}</div>
                    </div>
                @empty
                    <div class="text-soft">Belum ada koneksi yang dipetakan untuk modul ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
