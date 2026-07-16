<section>
    <header class="mb-4">
        <h3 class="h5 fw-bold text-danger mb-1">{{ __('Delete Account') }}</h3>
        <p class="text-soft mb-0">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Enter your password to confirm this action.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <div class="mb-3">
            <label for="delete_password" class="form-label">{{ __('Current Password') }}</label>
            <input
                id="delete_password"
                name="password"
                type="password"
                class="form-control @if ($errors->userDeletion->has('password')) is-invalid @endif"
                placeholder="{{ __('Enter your password') }}"
            >
            @if ($errors->userDeletion->has('password'))
                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-danger">{{ __('Delete Account Permanently') }}</button>
    </form>
</section>
