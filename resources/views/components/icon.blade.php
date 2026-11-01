@props(['name' => 'grid'])
@php
$paths = [
'grid'=>'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
'spool'=>'M7 4h10 M7 20h10 M8 4v16 M16 4v16 M8 8h8 M8 12h8 M8 16h8',
'building'=>'M4 21V5l8-3 8 3v16 M9 21v-5h6v5 M8 7h1 M15 7h1 M8 11h1 M15 11h1',
'layers'=>'m12 3 10 5-10 5L2 8z M2 12l10 5 10-5 M2 16l10 5 10-5',
'drop'=>'M12 3C9 7 5 11 5 15a7 7 0 0 0 14 0c0-4-4-8-7-12z M9 16c0 2 1 3 3 3',
'scissors'=>'M6 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-9 12-7M8.12 13.5 12 12m2.5-1.5L20 18',
'settings'=>'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M9 3h6l1 3 3 1 2 5-2 5-3 1-1 3H9l-1-3-3-1-2-5 2-5 3-1z',
'arrow'=>'M5 12h14 M13 6l6 6-6 6',
'plus'=>'M12 5v14 M5 12h14',
'pen'=>'M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z',
'trash'=>'M3 6h18 M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2 M10 11v6 M14 11v6',
'search'=>'M21 21l-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0z',
'sliders'=>'M4 21v-7 M4 10V3 M12 21v-9 M12 8V3 M20 21v-5 M20 12V3 M1 14h6 M9 8h6 M17 16h6',
'users'=>'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm14 10v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
'neck'=>'M6 4l6 4 6-4v16H6V4z M10 4a2 2 0 0 0 4 0',
'rib'=>'M4 6h16 M4 10h16 M4 14h16 M4 18h16',
'factory'=>'M2 20h20 M4 20V8l6 4V8l6 4V4l4 4v12',
];
@endphp
<svg {{ $attributes->merge(['class'=>'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['grid'] }}"/></svg>
