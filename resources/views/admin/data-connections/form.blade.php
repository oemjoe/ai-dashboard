<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">{{ $connection->exists ? 'Edit Data Connection' : 'Create Data Connection' }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $action }}" class="row g-3">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug', $connection->slug) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $connection->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">System Name</label>
            <input class="form-control" name="system_name" value="{{ old('system_name', $connection->system_name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Company</label>
            <select class="form-select" name="company_id">
                <option value="">Not scoped</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id', $connection->company_id) == $company->id)>{{ $company->code }} - {{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select class="form-select" name="db_driver">
                @foreach (['mysql', 'mariadb', 'sqlsrv'] as $driver)
                    <option value="{{ $driver }}" @selected(old('db_driver', $connection->db_driver) === $driver)>{{ strtoupper($driver) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Host</label>
            <input class="form-control" name="host" value="{{ old('host', $connection->host) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Port</label>
            <input class="form-control" name="port" value="{{ old('port', $connection->port ?: 3306) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Database</label>
            <input class="form-control" name="database_name" value="{{ old('database_name', $connection->database_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" value="{{ old('username', $connection->username) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Password Env Key</label>
            <input class="form-control" name="password_env_key" value="{{ old('password_env_key', $connection->password_env_key) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Module Slugs</label>
            <input class="form-control" name="module_slugs" value="{{ old('module_slugs', implode(',', $connection->module_slugs ?? [])) }}">
            <div class="form-text">Pisahkan dengan koma, misalnya: ar,ap,req</div>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $connection->is_active ?? true))>
                <label class="form-check-label">Active</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.data-connections.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
