<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-1">Reset Password</h2>
        <div class="text-muted">Masukkan email untuk menerima link reset password.</div>
    </div>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <button class="btn btn-primary w-100">Send Reset Link</button>
    </form>
</x-guest-layout>
