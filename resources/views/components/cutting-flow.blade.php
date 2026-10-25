@props(['type' => 'body'])
<div class="cutting-flow mb-6" aria-label="Cutting workflow">
    <div class="cutting-flow-step {{ $type === 'body' ? 'is-current' : '' }}"><span class="step">1</span><div><strong>Body cutting</strong><p class="hint">Returned rolls become body pieces</p></div></div>
    <x-icon name="arrow" class="text-muted"/>
    <div class="cutting-flow-step {{ $type === 'neck' ? 'is-current' : '' }}"><span class="step">2</span><div><strong>Neck cutting</strong><p class="hint">The same body pieces, the same dozens</p></div></div>
    <div class="cutting-flow-rib {{ $type === 'rib' ? 'is-current' : '' }}"><strong>Rib cutting</strong><p class="hint">Tracked from its returned rolls</p></div>
</div>
