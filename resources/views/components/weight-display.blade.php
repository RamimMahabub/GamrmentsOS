@props(['kg' => null, 'value' => null, 'scale' => 3, 'trim' => false])
@php
    $val = $kg ?? $value;
@endphp
@if($val === null || $val === '' || !is_numeric(str_replace(',', '', (string)$val)))
    <span>—</span>
@else
    @php
        $kgStr = \App\Support\Decimal::scale((string) $val, $scale);
        $displayKg = $trim && $scale > 0 ? rtrim(rtrim($kgStr, '0'), '.') : $kgStr;
        $lbVal = \App\Modules\Yarn\UnitConverter::fromKg((string) $val, 'lb', $scale);
        $displayLb = $trim && $scale > 0 ? rtrim(rtrim($lbVal, '0'), '.') : $lbVal;
    @endphp
    <span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-1']) }}>
        <span class="font-semibold tabular-nums text-ink">{{ $displayKg }} <span class="text-[10px] font-normal text-muted">kg</span></span>
        <span class="text-[11px] font-normal text-muted tabular-nums">({{ $displayLb }} lb)</span>
    </span>
@endif
