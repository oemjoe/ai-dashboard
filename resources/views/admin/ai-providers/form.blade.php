<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-uppercase text-soft small">Administration</div>
            <h2 class="h3 fw-bold mb-1">{{ $provider->exists ? 'Edit AI Provider' : 'Create AI Provider' }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $action }}" class="row g-3">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug', $provider->slug) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $provider->name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select class="form-select" name="driver">
                @foreach (['groq', 'gemini', 'ollama'] as $driver)
                    <option value="{{ $driver }}" @selected(old('driver', $provider->driver) === $driver)>{{ strtoupper($driver) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Model</label>
            <input class="form-control" name="model" value="{{ old('model', $provider->model) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Priority</label>
            <input class="form-control" name="priority" value="{{ old('priority', $provider->priority ?: 1) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Base URL</label>
            <input class="form-control" name="base_url" value="{{ old('base_url', $provider->base_url) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">API Key Env Key</label>
            <input class="form-control" name="api_key_env_key" value="{{ old('api_key_env_key', $provider->api_key_env_key) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Timeout</label>
            <input class="form-control" name="timeout_seconds" value="{{ old('timeout_seconds', $provider->timeout_seconds ?: 15) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Max Retries</label>
            <input class="form-control" name="max_retries" value="{{ old('max_retries', $provider->max_retries ?: 1) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Min Confidence</label>
            <input class="form-control" name="min_confidence" value="{{ old('min_confidence', $provider->min_confidence ?: 0.75) }}">
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $provider->is_enabled ?? true))>
                <label class="form-check-label">Enabled</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.ai-providers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
