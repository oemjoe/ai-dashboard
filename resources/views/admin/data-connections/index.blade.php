<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">Data Connections</h2>
            <div class="text-soft">Kelola koneksi database read-only untuk ERP, FMS, dan sistem pendukung.</div>
        </div>
    </x-slot>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.data-connections.create') }}" class="btn btn-primary">Add Connection</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Driver</th>
                    <th>Database</th>
                    <th>Read-only</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($connections as $connection)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $connection->name }}</div>
                            <div class="small text-soft">{{ $connection->system_name }}</div>
                        </td>
                        <td>{{ $connection->company?->code ?: '-' }}</td>
                        <td>{{ strtoupper($connection->db_driver) }}</td>
                        <td>{{ $connection->database_name }}</td>
                        <td>
                            <span class="badge text-bg-{{ $connection->is_read_only_validated ? 'success' : 'secondary' }}">
                                {{ $connection->is_read_only_validated ? 'Validated' : 'Not tested' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('admin.data-connections.test', $connection) }}">
                                    @csrf
                                    <button class="btn btn-outline-secondary btn-sm">Test</button>
                                </form>
                                <a href="{{ route('admin.data-connections.edit', $connection) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-soft">Belum ada data connection.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
