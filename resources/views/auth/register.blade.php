<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-1">Create Account</h2>
        <div class="text-muted">Default role baru adalah <strong>Viewer</strong>.</div>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Register</button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small">Already registered?</a>
        </div>
    </form>
</x-guest-layout>
