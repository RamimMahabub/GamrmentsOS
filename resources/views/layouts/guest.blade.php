<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#efeff2"><title>Sign in &middot; GarmentOS</title>@vite(['resources/css/app.css','resources/js/app.js']) @livewireStyles</head>
<body class="auth-page font-sans antialiased text-white selection:bg-[#d5ff27] selection:text-[#1b2000]">
    <div class="flex min-h-screen relative overflow-hidden">
        <!-- Subtle ambient radial lighting -->
        <div class="pointer-events-none absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-[#d5ff27] opacity-[0.035] blur-[140px]"></div>
        <div class="pointer-events-none absolute -bottom-40 right-10 h-[500px] w-[500px] rounded-full bg-[#d5ff27] opacity-[0.03] blur-[150px]"></div>

        <!-- Left Hero / Brand section -->
        <div class="auth-art hidden w-1/2 flex-col justify-between p-12 text-white lg:flex xl:p-16 border-r border-white/5 relative z-10">
            <div class="auth-orbit" aria-hidden="true"></div>
            
            <a href="{{ route('dashboard') }}" class="relative flex items-center gap-3 text-2xl font-semibold tracking-tight">
                <span class="brand-mark shadow-lg shadow-[#d5ff27]/10"><x-icon name="spool"/></span>
                <span class="text-white">Garment<span class="text-[#d5ff27]">OS</span></span>
            </a>

            <div class="relative py-12">
                <div class="eyebrow mb-6 inline-flex items-center gap-2 text-[#d5ff27] tracking-widest text-[11px] font-mono">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#d5ff27] animate-pulse"></span>
                    YOUR MANUFACTURING WORKSPACE
                </div>
                <h1 class="max-w-lg text-5xl leading-tight font-bold xl:text-6xl text-white tracking-tight">
                    Every thread.<br>
                    Every lot.<br>
                    <span class="text-[#d5ff27] drop-shadow-sm">A clearer picture.</span>
                </h1>
                <p class="mt-6 max-w-md text-base leading-7 text-neutral-400 font-normal">
                    Follow every kilogram with milligram precision across the complete apparel manufacturing lifecycle.
                </p>

                <!-- Full Functionalities & Modules Grid -->
                <div class="mt-8 grid grid-cols-2 gap-3 max-w-lg">
                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3 backdrop-blur-sm">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#d5ff27]/10 text-[#d5ff27] border border-[#d5ff27]/20">
                            <x-icon name="spool" class="h-4 w-4"/>
                        </span>
                        <div>
                            <div class="text-xs font-semibold text-white">Yarn Inventory</div>
                            <div class="text-[11px] text-neutral-400 leading-tight mt-0.5">Stock movements, bag tracking & supplier logs</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3 backdrop-blur-sm">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#d5ff27]/10 text-[#d5ff27] border border-[#d5ff27]/20">
                            <x-icon name="layers" class="h-4 w-4"/>
                        </span>
                        <div>
                            <div class="text-xs font-semibold text-white">Knitting Programs</div>
                            <div class="text-[11px] text-neutral-400 leading-tight mt-0.5">Multi-DIA tracking, wastage % & grey fabric</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3 backdrop-blur-sm">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#d5ff27]/10 text-[#d5ff27] border border-[#d5ff27]/20">
                            <x-icon name="drop" class="h-4 w-4"/>
                        </span>
                        <div>
                            <div class="text-xs font-semibold text-white">Dyeing & Finishing</div>
                            <div class="text-[11px] text-neutral-400 leading-tight mt-0.5">Color lots, roll weighing & shrinkage rates</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3 backdrop-blur-sm">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#d5ff27]/10 text-[#d5ff27] border border-[#d5ff27]/20">
                            <x-icon name="scissors" class="h-4 w-4"/>
                        </span>
                        <div>
                            <div class="text-xs font-semibold text-white">Cutting Section</div>
                            <div class="text-[11px] text-neutral-400 leading-tight mt-0.5">Body, Neck, and Rib cutting batches</div>
                        </div>
                    </div>
                </div>

                <!-- Process Flow Pills -->
                <div class="mt-6 flex flex-wrap items-center gap-2 text-[11px]">
                    <span class="pill px-2.5 py-1 font-medium rounded-full bg-white/5 border border-white/10 text-neutral-300">Warehouse</span>
                    <x-icon name="arrow" class="text-neutral-600 h-3 w-3"/>
                    <span class="pill px-2.5 py-1 font-medium rounded-full bg-white/5 border border-white/10 text-[#d5ff27]">Knitting</span>
                    <x-icon name="arrow" class="text-neutral-600 h-3 w-3"/>
                    <span class="pill px-2.5 py-1 font-medium rounded-full bg-white/5 border border-white/10 text-[#d5ff27]">Dyeing</span>
                    <x-icon name="arrow" class="text-neutral-600 h-3 w-3"/>
                    <span class="pill px-2.5 py-1 font-medium rounded-full bg-white/5 border border-white/10 text-[#d5ff27]">Cutting</span>
                    <x-icon name="arrow" class="text-neutral-600 h-3 w-3"/>
                    <span class="pill px-2.5 py-1 font-medium rounded-full bg-white/5 border border-white/10 text-neutral-300">Finished Garments</span>
                </div>
            </div>

            <div class="relative flex items-center gap-3 text-xs text-neutral-400">
                <span class="status-dot shadow-sm shadow-[#d5ff27]"></span>
                Built for complete floor visibility across Yarn, Mills, Dye Houses & Cutting.
            </div>
        </div>

        <!-- Right Login Card section -->
        <div class="flex flex-1 items-center justify-center px-6 py-12 sm:px-12 relative z-10">
            <div class="w-full max-w-md">
                <a href="{{ route('dashboard') }}" class="mb-8 flex items-center justify-center gap-3 text-xl font-semibold lg:hidden">
                    <span class="brand-mark shadow-lg shadow-[#d5ff27]/10"><x-icon name="spool"/></span>
                    <span class="text-white">Garment<span class="text-[#d5ff27]">OS</span></span>
                </a>

                <div class="auth-card">
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <span class="eyebrow inline-flex items-center gap-1.5 text-[#d5ff27] tracking-wider text-[11px] font-mono">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#d5ff27]"></span>
                                OWNER WORKSPACE
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-md bg-white/5 text-neutral-400 border border-white/5 font-mono">v1.0</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl text-white">Welcome back.</h1>
                        <p class="mt-2 text-sm text-neutral-400">Sign in to monitor live floor ops & manage workflows.</p>
                    </div>

                    {{ $slot }}
                </div>

                <div class="mt-6 flex items-center justify-center gap-2 text-xs text-neutral-500">
                    <x-icon name="spool" class="h-3.5 w-3.5 text-neutral-600"/>
                    <span>Your production. In focus.</span>
                </div>
            </div>
        </div>
    </div>
    @livewireScriptConfig
</body></html>
