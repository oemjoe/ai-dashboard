<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Executive Control Room</div>
            <h2 class="h3 fw-bold mb-1">Dashboard Overview</h2>
            <div class="text-soft">Ringkasan status koneksi, provider AI, audit, dan modul lintas fungsi.</div>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        @foreach ($headline_metrics as $metric)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card p-3 h-100">
                    <div class="text-soft small text-uppercase">{{ $metric['label'] }}</div>
                    <div class="display-6 fw-bold">{{ $metric['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="metric-card p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 fw-bold mb-0">Module Coverage</h3>
                    <a href="{{ route('help.index') }}" class="btn btn-outline-secondary btn-sm">Manual Book</a>
                </div>
                @foreach ($module_groups as $group)
                    <div class="mb-3">
                        <div class="small text-uppercase text-soft mb-2">{{ $group['group'] }}</div>
                        <div class="row g-3">
                            @foreach ($group['items'] as $module)
                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $module->name }}</div>
                                                <div class="text-soft small">{{ $module->description }}</div>
                                            </div>
                                            <span class="badge text-bg-warning">{{ str_replace('_', ' ', $module->mapping_status) }}</span>
                                        </div>
                                        <div class="mt-3">
                                            @can($module->permission_name)
                                                <a href="{{ route('modules.show', $module->slug) }}" class="btn btn-sm btn-primary">Open Module</a>
                                            @else
                                                <span class="badge text-bg-secondary">No access</span>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">Recent Audit Activity</h3>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_audits as $audit)
                                <tr>
                                    <td>{{ $audit->created_at?->format('d/m H:i') }}</td>
                                    <td>{{ $audit->action }}</td>
                                    <td>{{ $audit->user_name ?: 'system' }}</td>
                                    <td><span class="badge text-bg-{{ $audit->status === 'success' ? 'success' : 'danger' }}">{{ $audit->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-soft">Belum ada audit log.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="metric-card p-3 mb-4">
                <h3 class="h5 fw-bold mb-3">Data Connections</h3>
                @foreach ($connections as $connection)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="fw-semibold">{{ $connection->name }}</div>
                        <div class="small text-soft">{{ $connection->db_driver }} | {{ $connection->database_name }}</div>
                        <div class="small mt-1">
                            <span class="badge text-bg-{{ $connection->is_read_only_validated ? 'success' : 'secondary' }}">
                                {{ $connection->is_read_only_validated ? 'Read-only OK' : 'Not verified' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="metric-card p-3">
                <h3 class="h5 fw-bold mb-3">AI Providers</h3>
                @foreach ($providers as $provider)
                    <div class="border rounded-4 p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $provider['name'] }}</div>
                                <div class="small text-soft">{{ $provider['model'] ?: 'No model configured' }}</div>
                            </div>
                            <span class="badge text-bg-{{ $provider['status'] === 'ok' ? 'success' : ($provider['configured'] ? 'warning' : 'secondary') }}">
                                {{ $provider['status'] }}
                            </span>
                        </div>
                        <div class="small text-soft mt-2">{{ $provider['message'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
