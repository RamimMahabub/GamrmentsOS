<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Staff Directory</h1>
                <p class="text-xs text-muted">Manage cutting masters, floor operators, and track lifetime productivity statistics.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-primary" href="{{ route('staffs.create') }}">
                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                    Add Staff Member
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Compact Data Panel -->
    <div class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <!-- Compact Toolbar -->
        <div class="px-3.5 py-2 border-b border-line/70 bg-gradient-to-r from-surface via-raised/30 to-surface flex flex-wrap items-center justify-between gap-2.5">
            <form class="flex flex-wrap items-center gap-2" method="GET">
                <div class="relative min-w-[220px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-3.5 w-3.5"/>
                    </span>
                    <input id="list-search" 
                           type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search staff name or phone..." 
                           class="w-full pl-8 pr-3 py-1 text-xs bg-surface border border-line rounded-lg focus:border-accent transition">
                </div>

                <button type="submit" class="btn btn-secondary btn-xs">
                    <x-icon name="sliders" class="h-3 w-3 text-accent"/>
                    Search
                </button>
                @if(request()->has('q'))
                    <a href="{{ route('staffs.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-raised border border-line text-[11px] font-semibold text-ink">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse"></span>
                    {{ $staffs->total() }} Members Enrolled
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Staff Member</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Contact Phone</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Assigned Role</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Status</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Lifetime Output (Dozens / Pcs)</th>
                        <th class="number pr-4 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e4ea]">
                    @forelse($staffs as $index => $staff)
                        @php 
                            $totalPieces = $lifetimePieces[$staff->id] ?? '0.000'; 
                            $totalDozens = \App\Support\CuttingQuantity::dozens($totalPieces); 
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#edf3dc] even:bg-[#f7f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-[11px] text-muted">
                                {{ str_pad((string)($staffs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Staff Member Identity -->
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-line bg-surface text-ink font-bold text-[10px]">
                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                    </span>
                                    <a class="font-bold text-ink text-xs hover:text-accent transition" href="{{ route('staffs.show', $staff) }}">
                                        {{ $staff->name }}
                                    </a>
                                    <span class="text-[10px] text-muted font-mono">#ST-{{ $staff->id }}</span>
                                </div>
                            </td>

                            <!-- Phone -->
                            <td class="whitespace-nowrap text-xs font-mono text-ink">
                                {{ $staff->phone ?: '—' }}
                            </td>

                            <!-- Role -->
                            <td>
                                <span class="pill text-[10px] px-1.5 py-0.2 capitalize">
                                    {{ str_replace('_', ' ', $staff->role) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($staff->is_active)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-lime-50 text-lime-800 border border-lime-200">
                                        <span class="h-1 w-1 rounded-full bg-lime-600"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Lifetime Output -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">{{ $totalDozens }}</span> <span class="text-[10px] text-muted">doz</span>
                                <span class="text-[10px] text-muted font-mono ml-1">({{ \App\Support\Decimal::display($totalPieces) }} pcs)</span>
                            </td>

                            <!-- Actions -->
                            <td class="number whitespace-nowrap pr-4">
                                <div class="inline-flex items-center gap-1">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('staffs.show', $staff) }}">
                                        View
                                    </a>
                                    <a class="btn btn-secondary btn-xs" href="{{ route('staffs.edit', $staff) }}" title="Edit staff">
                                        Edit
                                    </a>
                                    @if($staff->is_active)
                                        <form method="POST" action="{{ route('staffs.destroy', $staff) }}" class="inline" x-data @submit="if(!confirm('Deactivate this staff member?')) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Deactivate staff" class="btn btn-danger btn-xs cursor-pointer">
                                                Deactivate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty py-10 text-center">
                                <h3 class="text-sm font-bold text-ink">No staff members enrolled</h3>
                                <p class="text-xs text-muted mt-1">Add cutting masters and floor staff to log cutting batches and track output metrics.</p>
                                <div class="mt-3">
                                    <a href="{{ route('staffs.create') }}" class="btn btn-primary btn-xs">
                                        <x-icon name="plus" class="h-3 w-3"/>
                                        Add First Staff Member
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-line/60 bg-raised/30">
            {{ $staffs->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
