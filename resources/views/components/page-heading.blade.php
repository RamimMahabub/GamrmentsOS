@props(['title','subtitle'=>null])
<div class="flex flex-wrap items-center justify-between gap-4"><div><h1>{{ $title }}</h1>@if($subtitle)<p class="mt-2 text-sm text-muted">{{ $subtitle }}</p>@endif</div><div class="flex items-center gap-2">{{ $slot }}</div></div>
