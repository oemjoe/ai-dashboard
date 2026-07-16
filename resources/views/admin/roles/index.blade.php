<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">Roles and Permissions</h2>
        </div>
    </x-slot>

    <div class="row g-3">
        @foreach ($roles as $role)
            <div class="col-12 col-lg-6">
                <div class="metric-card p-3 h-100">
                    <div class="fw-semibold">{{ $role->name }}</div>
                    <div class="text-soft small mt-2">{{ $role->permissions->pluck('name')->join(', ') }}</div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
