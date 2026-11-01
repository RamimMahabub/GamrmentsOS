@props(['action', 'label' => 'Delete', 'class' => 'text-xs font-medium text-danger hover:underline', 'icon' => null])
<form method="POST" action="{{ $action }}" class="inline" x-data @submit="if (!confirm('Delete this record? This action will preserve its audit history.')) $event.preventDefault()">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $class }}" title="{{ $label }}">
        @if($icon)
            <x-icon :name="$icon" class="h-3.5 w-3.5"/>
        @endif
        @if(!$icon || $slot->isNotEmpty())
            {{ $slot->isEmpty() ? $label : $slot }}
        @endif
    </button>
</form>

