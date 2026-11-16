@props(['title','subtitle'=>null])
<div class="flex flex-wrap items-center justify-between gap-3"><div><h1 class="text-xl font-bold tracking-tight text-ink">{{ $title }}</h1>@if($subtitle)<p class="text-xs text-muted mt-0.5">{{ $subtitle }}</p>@endif</div><div class="flex items-center gap-2">{{ $slot }}</div></div>
