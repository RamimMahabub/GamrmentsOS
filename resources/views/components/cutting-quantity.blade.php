@props(['row' => 'row', 'named' => false])
<div class="min-w-44 space-y-2">
    @if($named)
        <input type="hidden" :name="`rows[${index}][quantity_mode]`" value="dozen_pieces">
    @endif
    <div class="flex items-center gap-2">
        <label class="field flex-1 mb-0 text-xs">
            <span class="text-muted font-medium block mb-1">Dozen</span>
            <input class="w-20" aria-label="Dozen" type="number" min="0" step="1" x-model="{{ $row }}.quantity_dozens" @input="$nextTick(() => syncQuantity({{ $row }}))" @if($named) :name="`rows[${index}][quantity_dozens]`" @endif placeholder="0">
        </label>
        <label class="field flex-1 mb-0 text-xs">
            <span class="text-muted font-medium block mb-1">Pieces (pc)</span>
            <input class="w-20" aria-label="Pieces" type="number" min="0" step="1" x-model="{{ $row }}.quantity_pieces" @input="$nextTick(() => syncQuantity({{ $row }}))" @change="normalizePieces({{ $row }})" @blur="normalizePieces({{ $row }})" @if($named) :name="`rows[${index}][quantity_pieces]`" @endif placeholder="0">
        </label>
    </div>
    <p class="hint text-xs" x-text="'Selected: ' + quantity({{ $row }})"></p>
    <p class="hint text-xs" aria-live="polite" x-text="remainingForRow({{ $row }})"></p>
</div>
