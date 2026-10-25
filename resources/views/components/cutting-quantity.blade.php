@props(['row' => 'row', 'named' => false])
<div class="min-w-52 space-y-3">
    <label class="field">Quantity unit
        <select aria-label="Quantity unit" x-model="{{ $row }}.quantity_mode" @change="$nextTick(() => changeQuantityMode({{ $row }}))" @if($named) :name="`rows[${index}][quantity_mode]`" @endif>
            <template x-if="!{{ $row }}.quantity_mode"><option value="">Dozen (previous entry)</option></template>
            <option value="dozen">Dozen</option><option value="pieces">Pieces (pc)</option><option value="dozen_pieces">Dozen + pc</option>
        </select>
    </label>
    <div class="flex flex-wrap gap-3">
        <template x-if="{{ $row }}.quantity_mode && {{ $row }}.quantity_mode !== 'pieces'">
            <label class="field flex-1">Dozen<input class="w-24" aria-label="Dozen for neck" type="number" required min="0" step="1" x-model="{{ $row }}.quantity_dozens" @input="$nextTick(() => syncQuantity({{ $row }}))" @if($named) :name="`rows[${index}][quantity_dozens]`" @endif></label>
        </template>
        <template x-if="{{ $row }}.quantity_mode && {{ $row }}.quantity_mode !== 'dozen'">
            <label class="field flex-1">Pieces (pc)<input class="w-24" aria-label="Pieces for neck" type="number" required min="0" step="1" :max="{{ $row }}.quantity_mode === 'dozen_pieces' ? 11 : null" x-model="{{ $row }}.quantity_pieces" @input="$nextTick(() => syncQuantity({{ $row }}))" @if($named) :name="`rows[${index}][quantity_pieces]`" @endif></label>
        </template>
        <template x-if="!{{ $row }}.quantity_mode">
            <label class="field">Previously recorded dozen<input aria-label="Previously recorded dozen" type="number" required min="0.001" step="0.001" x-model="{{ $row }}.dozens" @input="$nextTick(() => syncQuantity({{ $row }}))" @if($named) :name="`rows[${index}][dozens]`" @endif></label>
        </template>
    </div>
    <p class="hint" x-text="'Selected: ' + quantity({{ $row }})"></p>
    <p class="hint" aria-live="polite" x-text="remainingForRow({{ $row }})"></p>
</div>
