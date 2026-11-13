<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Packaging Materials & Stock" subtitle="Manage inventory for poly bags, size stickers, other stickers, and 6-pc packet materials.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary" href="{{ route('packing-jobs.index') }}">&larr; Back to Packing</a>
                <a class="btn btn-primary" href="{{ route('packing-materials.create') }}">+ Add New Material</a>
            </div>
        </x-page-heading>
    </x-slot>

    <div class="space-y-6">
        <!-- Stock In Quick Form -->
        <section class="panel p-5" x-data="{ open: false }">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-ink uppercase tracking-wide">Record Stock In / Purchase</h3>
                    <p class="hint">Receive newly purchased poly bags, stickers, or packet materials into warehouse inventory.</p>
                </div>
                <button type="button" @click="open = !open" class="btn btn-secondary text-xs">
                    <span x-text="open ? 'Close form' : '+ Record incoming stock'"></span>
                </button>
            </div>

            <form x-show="open" x-cloak method="POST" action="{{ route('packing-materials.stock') }}" class="mt-4 pt-4 border-t border-line grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                @csrf
                <label class="field">
                    <span>Material item</span>
                    <select required name="packing_material_id">
                        <option value="">Choose material</option>
                        @foreach($materials as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ str_replace('_', ' ', $m->category) }}{{ $m->size_code ? ' - '.$m->size_code : '' }})</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    <span>Entry type</span>
                    <select required name="type">
                        <option value="purchase">Purchase</option>
                        <option value="opening">Opening balance</option>
                        <option value="adjustment">Inventory adjustment</option>
                    </select>
                </label>
                <label class="field">
                    <span>Quantity (units)</span>
                    <input type="number" step="1" required name="quantity" placeholder="e.g. 5000">
                </label>
                <label class="field">
                    <span>Date</span>
                    <input type="date" required name="movement_date" value="{{ now()->toDateString() }}">
                </label>
                <div>
                    <button type="submit" class="btn btn-primary w-full py-2 font-bold">Add to Stock</button>
                </div>
            </form>
        </section>

        <!-- Materials Catalog Table -->
        <section class="panel">
            <div class="panel-head flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2>Material catalog & balances</h2>
                    <p class="hint">Overview of all tracked packing materials and warehouse availability.</p>
                </div>
                <form method="GET" class="flex items-center gap-2 text-xs">
                    <select name="category" onchange="this.form.submit()" class="py-1 pl-2 pr-6 rounded-lg border border-line bg-surface text-ink text-xs font-medium">
                        <option value="">All categories</option>
                        <option value="poly_bag" @selected(request('category') == 'poly_bag')>Poly bags</option>
                        <option value="size_sticker" @selected(request('category') == 'size_sticker')>Size stickers</option>
                        <option value="other_sticker" @selected(request('category') == 'other_sticker')>Other stickers</option>
                        <option value="packet_material" @selected(request('category') == 'packet_material')>Packet materials</option>
                    </select>
                    @if(request('category'))
                        <a href="{{ route('packing-materials.index') }}" class="text-xs text-muted hover:text-accent font-medium">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Unit</th>
                            <th class="number font-bold">In-Stock Balance</th>
                            <th class="number">Alert threshold</th>
                            <th class="number">Cost / unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                            @php
                                $balance = $ledger->balance($material);
                                $isLow = \App\Support\Decimal::of($balance)->isLessThan((string) $material->min_stock_alert);
                            @endphp
                            <tr class="{{ $isLow ? 'bg-rose-50/40' : '' }}">
                                <td>
                                    <strong class="text-ink">{{ $material->name }}</strong>
                                </td>
                                <td>
                                    <span class="pill text-xs uppercase">{{ str_replace('_', ' ', $material->category) }}</span>
                                </td>
                                <td>
                                    @if($material->size_code)
                                        <span class="font-bold text-xs uppercase bg-[#eee] px-2 py-0.5 rounded">{{ $material->size_code }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted text-xs">
                                    {{ $material->unit }}
                                </td>
                                <td class="number font-bold {{ $isLow ? 'text-rose-700' : 'text-emerald-700' }}">
                                    {{ number_format((float) $balance) }} {{ $material->unit }}
                                    @if($isLow)
                                        <span class="text-[10px] text-rose-600 block">Low stock</span>
                                    @endif
                                </td>
                                <td class="number text-muted">
                                    {{ $material->min_stock_alert }}
                                </td>
                                <td class="number text-muted">
                                    {{ $material->cost_per_unit ? '$'.number_format((float)$material->cost_per_unit, 4) : '—' }}
                                </td>
                                <td>
                                    <span class="status-badge {{ $material->is_active ? 'status-badge-active' : 'status-badge-inactive' }}">
                                        {{ $material->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a class="link text-xs" href="{{ route('packing-materials.edit', $material) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-muted">
                                    No packing materials added yet.
                                    <div class="mt-2">
                                        <a class="btn btn-secondary text-xs" href="{{ route('packing-materials.create') }}">Create first material &rarr;</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($materials->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $materials->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>

