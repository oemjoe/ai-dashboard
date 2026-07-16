<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-1">Verify Email</h2>
        <div class="text-muted">Periksa inbox Anda lalu klik tautan verifikasi.</div>
    </div>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary w-100">Resend Verification Email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="btn btn-outline-secondary w-100">Logout</button>
    </form>
</x-guest-layout>
