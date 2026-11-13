<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="($material->exists ? 'Edit' : 'Add').' Packaging Material'" subtitle="Define packaging item for automated stock deduction.">
            <a class="btn btn-secondary" href="{{ route('packing-materials.index') }}">Back to list</a>
        </x-page-heading>
    </x-slot>

    <form method="POST" action="{{ $material->exists ? route('packing-materials.update', $material) : route('packing-materials.store') }}" class="max-w-2xl space-y-6">
        @csrf
        @if($material->exists)
            @method('PUT')
        @endif

        <section class="panel p-6 space-y-5">
            <label class="field">
                <span>Material name</span>
                <input type="text" required name="name" value="{{ old('name', $material->name) }}" placeholder="e.g. Poly Bag 10x14 or Size Sticker M">
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="field">
                    <span>Category</span>
                    <select required name="category" id="category-select" onchange="toggleSizeField(this.value)">
                        <option value="poly_bag" @selected(old('category', $material->category) == 'poly_bag')>Poly bag (1/piece)</option>
                        <option value="size_sticker" @selected(old('category', $material->category) == 'size_sticker')>Size sticker (1/piece)</option>
                        <option value="other_sticker" @selected(old('category', $material->category) == 'other_sticker')>Other sticker (1/piece)</option>
                        <option value="packet_material" @selected(old('category', $material->category) == 'packet_material')>Packet material (1/packet of 6)</option>
                    </select>
                </label>

                <label class="field" id="size-field">
                    <span>Size code (for size stickers)</span>
                    <input type="text" name="size_code" value="{{ old('size_code', $material->size_code) }}" placeholder="e.g. S, M, L, XL, 2XL">
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <label class="field">
                    <span>Unit of measure</span>
                    <input type="text" required name="unit" value="{{ old('unit', $material->unit ?? 'pcs') }}">
                </label>
                <label class="field">
                    <span>Cost per unit (optional)</span>
                    <input type="number" step="0.0001" min="0" name="cost_per_unit" value="{{ old('cost_per_unit', $material->cost_per_unit) }}" placeholder="0.0000">
                </label>
                <label class="field">
                    <span>Low stock alert threshold</span>
                    <input type="number" step="1" min="0" name="min_stock_alert" value="{{ old('min_stock_alert', $material->min_stock_alert ?? 100) }}">
                </label>
            </div>

            @if(!$material->exists)
                <label class="field">
                    <span>Initial opening stock (units)</span>
                    <input type="number" step="1" min="0" name="initial_stock" value="{{ old('initial_stock', 0) }}" placeholder="0">
                </label>
            @endif

            <label class="flex items-center gap-2 cursor-pointer pt-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $material->is_active ?? true))>
                <span class="text-sm font-medium text-ink">Active material item (available for auto-deduction)</span>
            </label>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('packing-materials.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary font-bold">
                    {{ $material->exists ? 'Update Material' : 'Save Material' }}
                </button>
            </div>
        </section>
    </form>
</x-app-layout>

