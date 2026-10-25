@props(['name' => 'grid'])
@php
$paths = [
'grid'=>'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
'spool'=>'M7 4h10 M7 20h10 M8 4v16 M16 4v16 M8 8h8 M8 12h8 M8 16h8',
'building'=>'M4 21V5l8-3 8 3v16 M9 21v-5h6v5 M8 7h1 M15 7h1 M8 11h1 M15 11h1',
'layers'=>'m12 3 10 5-10 5L2 8z M2 12l10 5 10-5 M2 16l10 5 10-5',
'drop'=>'M12 3C9 7 5 11 5 15a7 7 0 0 0 14 0c0-4-4-8-7-12z M9 16c0 2 1 3 3 3',
'settings'=>'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M9 3h6l1 3 3 1 2 5-2 5-3 1-1 3H9l-1-3-3-1-2-5 2-5 3-1z',
'arrow'=>'M5 12h14 M13 6l6 6-6 6',
'plus'=>'M12 5v14 M5 12h14',
];
@endphp
<svg {{ $attributes->merge(['class'=>'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['grid'] }}"/></svg>
