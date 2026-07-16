<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h3 fw-bold mb-1">{{ __('Profile Settings') }}</h2>
            <p class="text-soft mb-0">{{ __('Kelola identitas akun, email, password, dan kontrol penutupan akun.') }}</p>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="metric-card p-4 h-100">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="metric-card p-4 mb-4">
                @include('profile.partials.update-password-form')
            </div>

            <div class="metric-card p-4 border border-danger-subtle">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
