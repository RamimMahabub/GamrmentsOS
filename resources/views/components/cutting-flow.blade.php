@props(['type' => 'body', 'actionable' => null])
@php
    $isActionable = $actionable ?? request()->routeIs('cutting.*.index');
    $productParam = request('product');
    if ($type === 'cutting') {
        $workflow = 'underpants';
    } elseif ($type === 'body' || $type === 'neck') {
        $workflow = 'sando';
    } else { // type === 'rib'
        $workflow = $productParam === 'underpants' ? 'underpants' : 'sando';
    }

    $sandoSteps = [
        [
            'key' => 'body',
            'number' => 1,
            'icon' => 'scissors',
            'label' => 'Body Cutting',
            'short_label' => 'Body',
            'description' => 'Sando body pieces',
            'index_url' => route('cutting.body.index'),
            'create_url' => route('cutting.body.create'),
            'is_current' => $type === 'body',
        ],
        [
            'key' => 'neck',
            'number' => 2,
            'icon' => 'neck',
            'label' => 'Neck Cutting',
            'short_label' => 'Neck',
            'description' => 'Matching pieces & dozens (Sando)',
            'index_url' => route('cutting.neck.index'),
            'create_url' => route('cutting.neck.create'),
            'is_current' => $type === 'neck',
        ],
        [
            'key' => 'rib',
            'number' => 3,
            'icon' => 'rib',
            'label' => 'Rib Cutting (Sando)',
            'short_label' => 'Sando Rib',
            'description' => 'Tracked from rolls (Sando)',
            'index_url' => route('cutting.rib.index', ['product' => 'sando']),
            'create_url' => route('cutting.rib.create', ['product' => 'sando']),
            'is_current' => $type === 'rib' && $workflow === 'sando',
        ],
    ];

    $underpantsSteps = [
        [
            'key' => 'cutting',
            'number' => 1,
            'icon' => 'scissors',
            'label' => 'Underpants Cutting',
            'short_label' => 'Underpants',
            'description' => 'Boxer & Underwear pieces',
            'index_url' => route('cutting.cutting.index'),
            'create_url' => route('cutting.cutting.create'),
            'is_current' => $type === 'cutting',
        ],
        [
            'key' => 'rib',
            'number' => 2,
            'icon' => 'rib',
            'label' => 'Underpants Rib Cutting',
            'short_label' => 'Underpants Rib',
            'description' => 'Tracked from rolls (Boxer & Underwear)',
            'index_url' => route('cutting.rib.index', ['product' => 'underpants']),
            'create_url' => route('cutting.rib.create', ['product' => 'underpants']),
            'is_current' => $type === 'rib' && $workflow === 'underpants',
        ],
    ];
@endphp

<div>
    @if($isActionable)
        <section class="cutting-station-board" aria-label="Cutting workflow stations">
            <div class="cutting-station-board-heading flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="sewing-eyebrow">Production flow</div>
                    <h2>Choose a cutting step</h2>
                    @if($workflow === 'sando')
                        <p class="hint">Start at Body Cutting, then move matching pieces to Neck and Rib cutting.</p>
                    @else
                        <p class="hint">Underpants cutting flow: Cut ready fabric rolls, and process Rib cutting if lot includes rib.</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($workflow === 'sando')
                        <span class="cutting-flow-label hidden md:inline-flex">BODY CUTTING <b>→</b> NECK CUTTING <b>→</b> RIB CUTTING</span>
                    @else
                        <span class="cutting-flow-label hidden md:inline-flex">UNDERPANTS CUTTING <b>→</b> RIB CUTTING</span>
                    @endif
                    <div class="flex items-center gap-2 border-l border-line/60 pl-3">
                        <a href="{{ $type === 'rib' ? route('cutting.rib.index', ['product' => 'sando']) : route('cutting.body.index') }}"
                           class="btn btn-xs {{ $workflow === 'sando' ? 'btn-primary shadow-xs' : 'btn-secondary' }}">
                            Sando (Undershirt)
                        </a>
                        <a href="{{ $type === 'rib' ? route('cutting.rib.index', ['product' => 'underpants']) : route('cutting.cutting.index') }}"
                           class="btn btn-xs {{ $workflow === 'underpants' ? 'btn-primary shadow-xs' : 'btn-secondary' }}">
                            Underpants (Boxer / Underwear)
                        </a>
                    </div>
                </div>
            </div>

            @if($workflow === 'sando')
                <!-- Sando Cutting Stations -->
                <div class="cutting-station-grid">
                    @foreach($sandoSteps as $step)
                        <div class="cutting-station-card {{ $step['is_current'] ? 'active' : '' }}">
                            <a href="{{ $step['index_url'] }}" class="w-full flex-1 block focus:outline-none" title="View {{ $step['label'] }} list">
                                <div class="flex items-center justify-between w-full">
                                    <span class="cutting-station-number">{{ $step['number'] }}</span>
                                    @if($step['is_current'])
                                        <span class="pill text-[10px] font-bold text-[#526500] bg-[#f0f5df] border border-[#ccd994]">Current view</span>
                                    @else
                                        <span class="text-[11px] font-medium text-muted hover:text-ink">View table →</span>
                                    @endif
                                </div>
                                <span class="cutting-station-icon">
                                    <x-icon name="{{ $step['icon'] }}" class="h-7 w-7"/>
                                </span>
                                <strong>{{ $step['label'] }}</strong>
                                <span class="cutting-station-desc block">{{ $step['description'] }}</span>
                            </a>
                            <div class="cutting-station-btn">
                                <a href="{{ $step['create_url'] }}" class="btn {{ $step['is_current'] ? 'btn-primary shadow-sm shadow-accent/25' : 'btn-secondary' }} w-full justify-center text-xs font-semibold">
                                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                                    Add {{ $step['short_label'] }} Job
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Underpants Cutting Stations -->
                <div class="cutting-station-grid">
                    @foreach($underpantsSteps as $step)
                        <div class="cutting-station-card {{ $step['is_current'] ? 'active' : '' }}">
                            <a href="{{ $step['index_url'] }}" class="w-full flex-1 block focus:outline-none" title="View {{ $step['label'] }} list">
                                <div class="flex items-center justify-between w-full">
                                    <span class="cutting-station-number">{{ $step['number'] }}</span>
                                    @if($step['is_current'])
                                        <span class="pill text-[10px] font-bold text-[#526500] bg-[#f0f5df] border border-[#ccd994]">Current view</span>
                                    @else
                                        <span class="text-[11px] font-medium text-muted hover:text-ink">View table →</span>
                                    @endif
                                </div>
                                <span class="cutting-station-icon">
                                    <x-icon name="{{ $step['icon'] }}" class="h-7 w-7"/>
                                </span>
                                <strong>{{ $step['label'] }}</strong>
                                <span class="cutting-station-desc block">{{ $step['description'] }}</span>
                            </a>
                            <div class="cutting-station-btn">
                                <a href="{{ $step['create_url'] }}" class="btn {{ $step['is_current'] ? 'btn-primary shadow-sm shadow-accent/25' : 'btn-secondary' }} w-full justify-center text-xs font-semibold">
                                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                                    Add {{ $step['short_label'] }} Job
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <div class="cutting-flow mb-6 flex flex-wrap items-center justify-between gap-3 p-3 rounded-2xl border border-line bg-surface shadow-2xs" aria-label="Cutting workflow">
            @if($workflow === 'sando')
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('cutting.body.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'body' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'body' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">1</span>
                        <div>
                            <div class="text-xs font-bold text-ink">Body (Sando)</div>
                            <p class="text-[11px] text-muted">Rolls to body</p>
                        </div>
                    </a>
                    <x-icon name="arrow" class="text-muted/40 h-4 w-4 hidden sm:block"/>
                    <a href="{{ route('cutting.neck.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'neck' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'neck' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">2</span>
                        <div>
                            <div class="text-xs font-bold text-ink">Neck Cutting</div>
                            <p class="text-[11px] text-muted">Sando body & neck</p>
                        </div>
                    </a>
                    <x-icon name="arrow" class="text-muted/40 h-4 w-4 hidden sm:block"/>
                    <a href="{{ route('cutting.rib.index', ['product' => 'sando']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'rib' && $workflow === 'sando' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'rib' && $workflow === 'sando' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">3</span>
                        <div>
                            <div class="text-xs font-bold text-ink">Rib Cutting (Sando)</div>
                            <p class="text-[11px] text-muted">Tracked from rolls</p>
                        </div>
                    </a>
                </div>
            @else
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('cutting.cutting.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'cutting' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'cutting' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">1</span>
                        <div>
                            <div class="text-xs font-bold text-ink">Underpants Cutting</div>
                            <p class="text-[11px] text-muted">Boxer & Underwear</p>
                        </div>
                    </a>
                    <x-icon name="arrow" class="text-muted/40 h-4 w-4 hidden sm:block"/>
                    <a href="{{ route('cutting.rib.index', ['product' => 'underpants']) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'rib' && $workflow === 'underpants' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'rib' && $workflow === 'underpants' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">2</span>
                        <div>
                            <div class="text-xs font-bold text-ink">Underpants Rib Cutting</div>
                            <p class="text-[11px] text-muted">Optional rib component</p>
                        </div>
                    </a>
                </div>
            @endif

            <div class="flex items-center gap-1.5 ml-auto">
                <a href="{{ $type === 'rib' ? route('cutting.rib.index', ['product' => 'sando']) : route('cutting.body.index') }}"
                   class="btn btn-xs {{ $workflow === 'sando' ? 'btn-primary' : 'btn-secondary' }}">
                    Sando
                </a>
                <a href="{{ $type === 'rib' ? route('cutting.rib.index', ['product' => 'underpants']) : route('cutting.cutting.index') }}"
                   class="btn btn-xs {{ $workflow === 'underpants' ? 'btn-primary' : 'btn-secondary' }}">
                    Underpants
                </a>
            </div>
        </div>
    @endif
</div>
