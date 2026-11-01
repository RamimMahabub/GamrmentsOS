@props(['type' => 'body'])
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
