@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-line bg-canvas text-ink focus:border-accent focus:ring-accent']) }}>
