@props(['type' => 'body', 'actionable' => null])
@php
    $isActionable = $actionable ?? request()->routeIs('cutting.*.index');
    $steps = [
        [
            'key' => 'body',
            'number' => 1,
            'icon' => 'scissors',
            'label' => 'Body Cutting',
            'short_label' => 'Body',
            'description' => 'Rolls to body pieces',
        ],
        [
            'key' => 'neck',
            'number' => 2,
            'icon' => 'neck',
            'label' => 'Neck Cutting',
            'short_label' => 'Neck',
            'description' => 'Matching pieces & dozens',
        ],
        [
            'key' => 'rib',
            'number' => 3,
            'icon' => 'rib',
            'label' => 'Rib Cutting',
            'short_label' => 'Rib',
            'description' => 'Tracked from returned rolls',
        ],
    ];
@endphp

@if($isActionable)
    <section class="cutting-station-board" aria-label="Cutting workflow stations">
        <div class="cutting-station-board-heading">
            <div>
                <div class="sewing-eyebrow">Production flow</div>
                <h2>Choose a cutting step</h2>
                <p class="hint">Start at Body Cutting, then move matching pieces to Neck and Rib cutting.</p>
            </div>
            <span class="cutting-flow-label">BODY CUTTING <b>→</b> NECK CUTTING <b>→</b> RIB CUTTING</span>
        </div>
        <div class="cutting-station-grid">
            @foreach($steps as $step)
                <div class="cutting-station-card {{ $type === $step['key'] ? 'active' : '' }}">
                    <a href="{{ route('cutting.'.$step['key'].'.index') }}" class="w-full flex-1 block focus:outline-none" title="View {{ $step['label'] }} list">
                        <div class="flex items-center justify-between w-full">
                            <span class="cutting-station-number">{{ $step['number'] }}</span>
                            @if($type === $step['key'])
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
                        <a href="{{ route('cutting.'.$step['key'].'.create') }}" class="btn {{ $type === $step['key'] ? 'btn-primary shadow-sm shadow-accent/25' : 'btn-secondary' }} w-full justify-center text-xs font-semibold">
                            <x-icon name="plus" class="h-3.5 w-3.5"/>
                            Add {{ $step['short_label'] }} Cutting Job
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@else
    <div class="cutting-flow mb-6 flex flex-wrap items-center gap-3 p-3 rounded-2xl border border-line bg-surface shadow-2xs" aria-label="Cutting workflow">
        <a href="{{ route('cutting.body.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'body' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'body' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">1</span>
            <div>
                <div class="text-xs font-bold text-ink">Body Cutting</div>
                <p class="text-[11px] text-muted">Rolls to body pieces</p>
            </div>
        </a>
        <x-icon name="arrow" class="text-muted/40 h-4 w-4 hidden sm:block"/>
        <a href="{{ route('cutting.neck.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'neck' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $type === 'neck' ? 'bg-[#d5ff27] text-[#222800]' : 'bg-raised text-muted' }}">2</span>
            <div>
                <div class="text-xs font-bold text-ink">Neck Cutting</div>
                <p class="text-[11px] text-muted">Matching pieces & dozens</p>
            </div>
        </a>
        <a href="{{ route('cutting.rib.index') }}" class="sm:ml-auto flex items-center gap-3 px-4 py-2.5 rounded-xl transition duration-150 {{ $type === 'rib' ? 'bg-[#d5ff27]/20 border border-[#ccd994] text-ink shadow-2xs' : 'hover:bg-raised text-muted hover:text-ink' }}">
            <x-icon name="rib" class="h-4 w-4 text-muted"/>
            <div>
                <div class="text-xs font-bold text-ink">Rib Cutting</div>
                <p class="text-[11px] text-muted">Tracked from returned rolls</p>
            </div>
        </a>
    </div>
@endif
