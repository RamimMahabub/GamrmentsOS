<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Lot-Specific Packaging Materials" subtitle="Assign specific poly bags, size stickers, other stickers, and master packets for each production lot.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary" href="{{ route('packing-jobs.create') }}">&larr; Back to Packing Form</a>
                <a class="btn btn-secondary" href="{{ route('packing-materials.index') }}">Materials Inventory</a>
            </div>
        </x-page-heading>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <!-- Lots Sidebar -->
        <div class="panel p-4 space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted px-2">Select a Lot</h3>
            @forelse($lots as $lotItem)
                <a href="{{ route('lots.packing-materials.index', ['lot_id' => $lotItem->id]) }}" 
                   class="flex items-center justify-between p-2.5 rounded-lg border text-xs transition {{ ($activeLot && $activeLot->id === $lotItem->id) ? 'bg-ink text-white font-bold border-ink shadow-sm' : 'border-line/70 bg-surface hover:bg-raised text-ink' }}">
                    <span class="font-mono">{{ $lotItem->lot_number }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeLot && $activeLot->id === $lotItem->id) ? 'bg-white/20 text-white' : 'bg-[#eee] text-muted' }}">
                        {{ $lotItem->packingMaterials->count() }} set
                    </span>
                </a>
            @empty
                <p class="text-xs text-muted p-2">No lots found.</p>
            @endforelse
        </div>

        <!-- Materials Configuration Panel for Selected Lot -->
        <div class="lg:col-span-3">
            @if($activeLot)
                <form method="POST" action="{{ route('lots.packing-materials.update', $activeLot) }}" class="panel p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Configuring Lot</span>
                            <h2 class="text-lg font-mono font-black text-ink">{{ $activeLot->lot_number }}</h2>
                            <p class="hint">Select the materials that will be consumed when packing batches from this lot. If left empty, packing for this lot will not enforce materials.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('lots.show', $activeLot) }}" class="btn btn-secondary btn-xs">View Lot Trace &nearr;</a>
                            <button type="submit" class="btn btn-primary">Save Lot Materials</button>
                        </div>
                    </div>

                    @php
                        $assignedIds = $activeLot->packingMaterials->pluck('id')->all();
                        $byCategory = $materials->groupBy('category');
                        $catLabels = [
                            'poly_bag' => '1. Poly Bags (1 per piece)',
                            'size_sticker' => '2. Size Stickers (1 per piece by size)',
                            'other_sticker' => '3. Other Stickers / Barcodes (1 per piece)',
                            'packet_material' => '4. Packet Materials (1 per 6 pieces)',
                        ];
                    @endphp

                    @foreach(['poly_bag', 'size_sticker', 'other_sticker', 'packet_material'] as $catKey)
                        <div class="space-y-3">
                            <h3 class="font-bold text-xs uppercase tracking-wider text-ink border-l-2 border-accent pl-2">
                                {{ $catLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey)) }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
                                @forelse($byCategory->get($catKey, collect()) as $m)
                                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border border-line bg-raised/20 hover:bg-surface cursor-pointer text-xs">
                                        <input type="checkbox" name="material_ids[]" value="{{ $m->id }}" @checked(in_array($m->id, $assignedIds)) class="mt-0.5">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-ink truncate">{{ $m->name }}</div>
                                            <div class="text-[10px] text-muted mt-0.5 flex items-center justify-between">
                                                <span>{{ $m->size_code ? 'Size: '.$m->size_code : 'Standard' }}</span>
                                                <span class="font-semibold {{ $m->available_stock > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                                    {{ number_format($m->available_stock) }} {{ $m->unit }} stock
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="col-span-full p-2.5 rounded border border-dashed border-line text-xs text-muted">
                                        No active {{ str_replace('_', ' ', $catKey) }} items in system. <a class="link" href="{{ route('packing-materials.create') }}">Create one &rarr;</a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between border-t border-line pt-4">
                        <span class="text-xs text-muted">Saving will apply these materials whenever {{ $activeLot->lot_number }} is packed.</span>
                        <button type="submit" class="btn btn-primary">Save Lot Materials</button>
                    </div>
                </form>
            @else
                <div class="panel p-8 text-center text-muted">
                    No lot selected. Please create or select a lot first.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
