<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-1">Confirm Password</h2>
        <div class="text-muted">Masukkan password untuk melanjutkan.</div>
    </div>
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Confirm</button>
    </form>
</x-guest-layout>
