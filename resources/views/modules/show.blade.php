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
            <span class="badge text-bg-{{ $mapping_status === 'live_analysis_ready' ? 'success' : 'warning' }}">{{ str_replace('_', ' ', $mapping_status) }}</span>
            <span class="text-soft small ms-2">Data as of: {{ optional($data_as_of)->format('d M Y H:i') ?: 'Belum tersedia' }}</span>
        </div>
        <a href="{{ route('help.show', $module->guide_slug ?: 'getting-started') }}" class="btn btn-outline-secondary btn-sm">Panduan Modul</a>
    </div>

    <div class="metric-card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 fw-bold mb-0">Live Filters</h3>
            <a href="{{ route('modules.show', $module->slug) }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
        </div>
        <form method="GET" action="{{ route('modules.show', $module->slug) }}">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label small text-uppercase text-soft">Keyword</label>
                    <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="form-control" placeholder="Kode, invoice, customer">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Company</label>
                    <input type="text" name="company" value="{{ $filters['company'] ?? '' }}" class="form-control" placeholder="TNP">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Division</label>
                    <input type="text" name="division" value="{{ $filters['division'] ?? '' }}" class="form-control" placeholder="Finance">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Branch</label>
                    <input type="text" name="branch" value="{{ $filters['branch'] ?? '' }}" class="form-control" placeholder="Branch">
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <label class="form-label small text-uppercase text-soft">Location</label>
                    <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" class="form-control" placeholder="Location">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm">Apply Filter</button>
                <span class="text-soft small align-self-center">Filter diterapkan secara read-only ke analisa live modul ini jika kolom yang sesuai tersedia di sumber data.</span>
            </div>
        </form>
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
                <h3 class="h5 fw-bold mb-3">Enterprise Findings</h3>
                @foreach ($findings as $finding)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if (!empty($finding['category']))
                                    <div class="text-soft small text-uppercase mb-1">{{ $finding['category'] }}</div>
                                @endif
                                <div class="fw-semibold">{{ $finding['title'] }}</div>
                            </div>
                            <span class="badge text-bg-{{ $finding['severity'] === 'warning' ? 'warning' : 'info' }}">{{ $finding['severity'] }}</span>
                        </div>
                        <div class="text-soft small mt-2">{{ $finding['description'] }}</div>
                        @if (!empty($finding['action']))
                            <div class="small mt-2">
                                <span class="fw-semibold">Priority action:</span>
                                <span class="text-soft">{{ $finding['action'] }}</span>
                            </div>
                        @endif
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

            @if (!empty($data_preview))
                <div class="metric-card p-3 mt-4">
                    <h3 class="h5 fw-bold mb-3">{{ $data_preview['label'] }}</h3>
                    <div class="small text-soft mb-3">Preview ini diambil langsung dari sumber data live dengan koneksi read-only.</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    @foreach ($data_preview['columns'] as $column)
                                        <th>{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data_preview['rows'] as $row)
                                    <tr>
                                        @foreach ($data_preview['columns'] as $column)
                                            <td>{{ $row[$column] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($data_preview['columns']) }}" class="text-soft">Tidak ada row yang cocok dengan filter saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
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
