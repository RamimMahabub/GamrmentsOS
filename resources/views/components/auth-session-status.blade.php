@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-accent']) }}>
        {{ $status }}
    </div>
@endif
