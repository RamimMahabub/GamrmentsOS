<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#efeff2"><title>Sign in &middot; GarmentOS</title>@vite(['resources/css/app.css','resources/js/app.js']) @livewireStyles</head>
<body class="font-sans antialiased">
    <div class="flex min-h-screen">
        <div class="auth-art hidden w-1/2 flex-col justify-between p-12 text-ink lg:flex xl:p-16">
            <div class="auth-orbit" aria-hidden="true"></div>
            <a href="{{ route('dashboard') }}" class="relative flex items-center gap-3 text-2xl font-semibold tracking-tight"><span class="brand-mark"><x-icon name="spool"/></span><span>Garment<span class="text-accent">OS</span></span></a>
            <div class="relative py-12"><div class="eyebrow mb-6 text-accent">Your manufacturing workspace</div><h1 class="max-w-lg text-5xl leading-tight xl:text-6xl">Every thread.<br>Every lot.<br><span class="text-accent">A clearer picture.</span></h1><p class="mt-7 max-w-sm text-base leading-7 text-muted">Follow your yarn from the warehouse through knitting and dyeing, with every kilogram accounted for.</p><div class="mt-10 flex flex-wrap items-center gap-3 text-xs"><span class="pill">Yarn</span><x-icon name="arrow" class="text-faint"/><span class="pill">Knitting</span><x-icon name="arrow" class="text-faint"/><span class="pill">Dyeing</span></div></div>
            <div class="relative flex items-center gap-3 text-xs text-muted"><span class="status-dot"></span>Built for the way your factory works.</div>
        </div>
        <div class="flex flex-1 items-center justify-center px-5 py-12 sm:px-10"><div class="w-full max-w-md"><a href="{{ route('dashboard') }}" class="mb-8 flex items-center justify-center gap-3 text-xl font-semibold lg:hidden"><span class="brand-mark"><x-icon name="spool"/></span>Garment<span class="-ml-3 text-accent">OS</span></a><div class="auth-card"><div class="mb-9"><span class="eyebrow mb-4 block text-accent">Owner workspace</span><h1>Welcome back.</h1><p class="mt-3 text-sm text-muted">Sign in to keep your production moving.</p></div>{{ $slot }}</div><p class="mt-6 text-center text-xs text-muted">Your production. In focus.</p></div></div>
    </div>
    @livewireScriptConfig
</body></html>
