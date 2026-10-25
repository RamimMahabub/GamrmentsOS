<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="my-5 flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-line text-accent focus:ring-accent" name="remember">
                <span class="text-xs text-muted">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="link text-xs" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>
        <x-primary-button class="w-full">{{ __('Log in') }}<x-icon name="arrow" class="h-4 w-4"/></x-primary-button>
    </form>
</x-guest-layout>
