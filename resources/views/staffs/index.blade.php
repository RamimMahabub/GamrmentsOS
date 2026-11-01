<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Floor Staff & Workforce
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Staff Directory</h1>
                <p class="text-sm text-muted mt-1">Manage cutting masters, floor operators, and track lifetime productivity statistics.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-primary shadow-sm shadow-accent/25" href="{{ route('staffs.create') }}">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Add Staff Member
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Futuristic Data Panel -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <!-- Futuristic Toolbar -->
        <div class="px-6 py-5 border-b border-line/70 bg-gradient-to-r from-surface via-raised/40 to-surface flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap items-center gap-3" method="GET">
                <div class="relative min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-4 w-4"/>
                    </span>
                    <input id="list-search" 
                           type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search staff name or phone..." 
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface border border-line rounded-xl focus:border-accent focus:ring-2 focus:ring-accent/20 transition shadow-2xs">
                </div>

                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                    <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                    Search
                </button>
                @if(request()->has('q'))
                    <a href="{{ route('staffs.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-raised border border-line text-xs font-semibold text-ink shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    {{ $staffs->total() }} Members Enrolled
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Staff Member</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Contact Phone</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Assigned Role</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Status</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Lifetime Output (Dozens / Pcs)</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeff3]">
                    @forelse($staffs as $index => $staff)
                        @php 
                            $totalPieces = $lifetimePieces[$staff->id] ?? '0.000'; 
                            $totalDozens = \App\Support\CuttingQuantity::dozens($totalPieces); 
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#fafaf3]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($staffs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Staff Member Identity -->
                            <td>
                                <div class="flex items-center gap-3.5 py-1">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface text-ink font-bold text-xs shadow-2xs group-hover:border-accent-line group-hover:text-accent transition duration-150">
                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a class="font-bold text-ink text-sm tracking-tight hover:text-accent transition" href="{{ route('staffs.show', $staff) }}">
                                            {{ $staff->name }}
                                        </a>
                                        <div class="text-[11px] text-muted font-mono mt-0.5">ID: #ST-{{ $staff->id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phone -->
                            <td class="whitespace-nowrap text-xs font-mono text-ink">
                                {{ $staff->phone ?: '—' }}
                            </td>

                            <!-- Role -->
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-surface border border-line font-medium text-xs text-ink shadow-2xs capitalize">
                                    {{ str_replace('_', ' ', $staff->role) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($staff->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-lime-50 text-lime-800 border border-lime-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-lime-600"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Lifetime Output -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $totalDozens }} <span class="text-xs font-normal text-muted">doz</span>
                                </div>
                                <div class="text-[11px] text-muted font-mono mt-0.5">
                                    {{ \App\Support\Decimal::display($totalPieces) }} pcs
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="number whitespace-nowrap pr-6">
                                <div class="inline-flex items-center gap-1.5">
                                    <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent shadow-2xs transition" href="{{ route('staffs.show', $staff) }}">
                                        View
                                    </a>
                                    <a class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-line bg-surface text-muted hover:text-ink hover:border-muted transition shadow-2xs" href="{{ route('staffs.edit', $staff) }}" title="Edit staff">
                                        <x-icon name="pen" class="h-3.5 w-3.5"/>
                                    </a>
                                    @if($staff->is_active)
                                        <form method="POST" action="{{ route('staffs.destroy', $staff) }}" class="inline" x-data @submit="if(!confirm('Deactivate this staff member?')) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Deactivate staff" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs cursor-pointer">
                                                <x-icon name="trash" class="h-3.5 w-3.5"/>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty py-20 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-soft text-accent-ink mb-4 shadow-sm">
                                    <x-icon name="users" class="h-8 w-8"/>
                                </div>
                                <h3 class="text-lg font-bold text-ink">No staff members enrolled</h3>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">Add cutting masters and floor staff to log cutting batches and track output metrics.</p>
                                <div class="mt-5">
                                    <a href="{{ route('staffs.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                                        <x-icon name="plus" class="h-4 w-4"/>
                                        Add First Staff Member
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-line/60 bg-raised/30">
            {{ $staffs->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>