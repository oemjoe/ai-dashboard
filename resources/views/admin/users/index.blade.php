<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">Users</h2>
        </div>
    </x-slot>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Roles</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->company?->code ?: '-' }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                        <td>{{ optional($user->last_login_at)->format('d/m/Y H:i') ?: 'Never' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
