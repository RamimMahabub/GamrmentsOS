<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#efeff2">
    <title>{{ config('app.name', 'GarmentOS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="workspace-shell font-sans antialiased" x-data="{sidebar:false, collapsed:false, desktop:window.matchMedia('(min-width: 1024px)').matches, init(){try{this.collapsed=localStorage.getItem('garmentos-sidebar-collapsed')==='true'}catch{}}, toggleSidebar(){this.collapsed=!this.collapsed;try{localStorage.setItem('garmentos-sidebar-collapsed',String(this.collapsed))}catch{}}}" :class="{'sidebar-collapsed':collapsed && desktop}" @resize.window="desktop=window.matchMedia('(min-width: 1024px)').matches" @keydown.escape.window="sidebar=false">
    <button x-show="sidebar" x-cloak @click="sidebar=false" class="fixed inset-0 z-30 bg-black/70 backdrop-blur-sm lg:hidden" aria-label="Close navigation"></button>
    <aside x-data="{scrolling:false, scrollTimer:null}" @scroll.passive="scrolling=true; clearTimeout(scrollTimer); scrollTimer=setTimeout(()=>scrolling=false,800)" id="workspace-navigation" class="app-sidebar fixed z-40 flex flex-col overflow-y-auto py-6 transition-transform lg:translate-x-0" :class="[sidebar ? 'translate-x-0' : '-translate-x-[110%]', {'is-scrolling':scrolling}]" :inert="!sidebar && !desktop">
        <div class="sidebar-brand flex items-center justify-between gap-2 px-4">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 text-lg font-semibold tracking-tight text-ink" aria-label="GarmentOS dashboard">
                <span class="brand-mark"><x-icon name="spool"/></span><span class="sidebar-copy">Garment<span class="text-accent">OS</span></span>
            </a>
            <button type="button" class="sidebar-toggle hidden lg:flex" @click="toggleSidebar()" :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'" :aria-expanded="!collapsed" aria-controls="workspace-navigation" :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"><svg class="h-4 w-4 transition-transform" :class="collapsed ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 5-5 5 5 5"/></svg></button>
        </div>
        <div class="eyebrow sidebar-copy mt-9 px-7">Workspace</div>
        <nav class="sidebar-nav mt-5 space-y-2" aria-label="Main navigation">
            @foreach(config('navigation') as $item)
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}" @if(request()->routeIs($item['active'])) aria-current="page" @endif class="nav-item {{ request()->routeIs($item['active']) ? 'active' : '' }}"><span class="nav-icon"><x-icon :name="$item['icon']"/></span><span class="sidebar-copy">{{ $item['label'] }}</span></a>
            @endforeach
        </nav>
        <div class="sidebar-copy mt-auto px-4 pt-8"><div class="workspace-card p-4">
            <div class="mb-2 flex items-center gap-2 text-xs font-medium text-ink"><span class="status-dot"></span> Manufacturing workspace</div>
            <p class="text-xs leading-5 text-muted">From yarn to dyed fabric.<br>Every lot, every kilogram.</p>
        </div></div>
        <a href="{{ route('profile.edit') }}" title="Your account" aria-label="Your account" class="sidebar-account mx-4 mt-5 flex items-center gap-3 rounded-xl p-2 transition hover:bg-hover">
            <span class="avatar h-9 w-9 shrink-0">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
            <span class="sidebar-copy min-w-0"><span class="block truncate text-xs font-medium text-ink">{{ auth()->user()->name }}</span><span class="text-[11px] text-muted">Owner workspace</span></span>
        </a>
    </aside>
    <div class="workspace-main">
        <header class="app-topbar sticky top-0 z-20 flex h-[76px] items-center justify-between px-5 lg:px-9">
            <div class="flex items-center gap-3"><button @click="sidebar=true" class="btn btn-secondary lg:hidden" aria-label="Open navigation" aria-controls="workspace-navigation" :aria-expanded="sidebar"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14"/></svg></button><span class="text-xs text-muted"><span class="hidden sm:inline">Workspace <span class="mx-3 text-faint">/</span></span><span class="font-medium text-ink">{{ collect(config('navigation'))->first(fn($item)=>request()->routeIs($item['active']))['label'] ?? 'Lot trace' }}</span></span></div>
            <div class="flex items-center gap-5"><span class="hidden text-xs text-muted sm:block">{{ now()->format('d M Y') }}</span><div x-data="{open:false}" class="relative" @keydown.escape.stop="open=false"><button @click="open=!open" class="avatar h-9 w-9" aria-label="User menu" :aria-expanded="open">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</button><div x-show="open" x-cloak @click.outside="open=false" x-transition.opacity class="panel absolute right-0 z-50 mt-3 w-44 p-2"><a class="menu-item" href="{{ route('profile.edit') }}">Account</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="menu-item">Sign out</button></form></div></div></div>
        </header>
        <main class="mx-auto max-w-[1600px] px-5 py-8 lg:px-9 lg:py-10">
            @isset($header)<div class="mb-8">{{ $header }}</div>@endisset
            @if(session('status'))<div role="status" class="mb-6 rounded-xl border border-accent-line bg-accent-soft px-5 py-3 text-sm text-accent">{{ session('status') }}</div>@endif
            @if($errors->any())<div role="alert" class="mb-6 rounded-xl border border-danger-line bg-danger-soft p-4 text-danger"><strong>Check these details before saving:</strong><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            {{ $slot }}
            <footer class="mt-10 flex flex-wrap justify-between gap-3 border-t border-line pt-5 text-[11px] text-muted"><span>GarmentOS &middot; Manufacturing, connected.</span><span>Weights in kg &middot; Display in {{ strtoupper($defaultUnit ?? 'lb') }}</span></footer>
        </main>
    </div>
    @livewireScriptConfig
</body>
</html>
