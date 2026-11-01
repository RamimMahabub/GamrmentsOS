<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Login ID or Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-neutral-300 mb-1.5">
                {{ __('Login ID or email') }}
            </label>
            <div class="relative">
                <input id="email" 
                       type="text" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       placeholder="e.g. owner@garmentos.test"
                       class="block w-full text-sm" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-neutral-300">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-neutral-400 hover:text-[#d5ff27] transition-colors" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="relative" x-data="{ show: false }">
                <input id="password" 
                       :type="show ? 'text' : 'password'"
                       name="password"
                       required 
                       autocomplete="current-password"
                       placeholder="••••••••••••"
                       class="block w-full text-sm pr-10" />
                <button type="button" 
                        @click="show = !show" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-white transition p-1"
                        tabindex="-1">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
        </div>

        <div class="my-5 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4">
                <span class="text-xs text-neutral-300 font-normal">{{ __('Remember this device') }}</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full shadow-lg shadow-[#d5ff27]/15 hover:shadow-[#d5ff27]/25 font-semibold text-sm h-11 tracking-wide">
            <span>{{ __('Sign In to GarmentOS') }}</span>
            <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-0.5"/>
        </button>

        @if(app()->environment(['local', 'testing']))
            <!-- Quick Demo Credentials Fill -->
            <div class="mt-6 pt-5 border-t border-white/10" 
                 x-data="{
                     fill(email, pass) {
                         const emailInput = document.getElementById('email');
                         const passInput = document.getElementById('password');
                         if(emailInput) { emailInput.value = email; emailInput.dispatchEvent(new Event('input')); }
                         if(passInput) { passInput.value = pass; passInput.dispatchEvent(new Event('input')); }
                     }
                 }">
                <div class="flex items-center justify-between text-[11px] text-neutral-400 mb-2.5">
                    <span class="font-medium uppercase tracking-wider text-neutral-400">Demo Access</span>
                    <span class="text-neutral-500">Click to autofill</span>
                </div>
                <button type="button" 
                        @click="fill('owner@garmentos.test', 'ChangeMe2026!')" 
                        class="w-full flex items-center justify-between p-2.5 rounded-xl border border-white/10 bg-white/[0.02] hover:bg-white/[0.06] hover:border-[#d5ff27]/40 transition text-left group">
                    <div class="flex items-center gap-2.5">
                        <span class="h-7 w-7 rounded-lg bg-[#d5ff27]/10 text-[#d5ff27] flex items-center justify-center font-bold text-xs">
                            O
                        </span>
                        <div>
                            <div class="text-xs font-medium text-white group-hover:text-[#d5ff27] transition">Owner Account</div>
                            <div class="text-[11px] text-neutral-400 font-mono">owner@garmentos.test</div>
                        </div>
                    </div>
                    <span class="text-[11px] text-neutral-400 group-hover:text-white px-2 py-0.5 rounded bg-white/5 border border-white/5 font-mono">Autofill</span>
                </button>
            </div>
        @endif
    </form>
</x-guest-layout>
