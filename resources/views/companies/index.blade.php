<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Manufacturing Partner Ecosystem
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Companies</h1>
                <p class="text-sm text-muted mt-1">External partner directory: circular knitters, finishing houses, and primary contacts.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-primary shadow-sm shadow-accent/25" href="{{ route('companies.create') }}">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Add Company
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Futuristic Data Panel -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <!-- Futuristic Toolbar -->
        <div class="px-6 py-5 border-b border-line/70 bg-gradient-to-r from-surface via-raised/40 to-surface flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-4 w-4"/>
                    </span>
                    <input id="list-search" 
                           type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search company, address..." 
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface border border-line rounded-xl focus:border-accent focus:ring-2 focus:ring-accent/20 transition shadow-2xs">
                </div>
                
                <div class="flex items-center gap-2">
                    <select name="type" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="">All Partner Types</option>
                        @foreach(['knitting','dyeing','both'] as $type)
                            <option value="{{ $type }}" @selected(request('type')===$type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>

                    <select id="list-sort" name="sort" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="name" @selected(request('sort','name')==='name')>Sort: Company Name</option>
                        <option value="address" @selected(request('sort')==='address')>Sort: Address</option>
                        <option value="is_active" @selected(request('sort')==='is_active')>Sort: Status</option>
                    </select>

                    <select name="direction" aria-label="Sort direction" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="asc" @selected(request('direction','asc')==='asc')>Ascending ↑</option>
                        <option value="desc" @selected(request('direction')==='desc')>Descending ↓</option>
                    </select>

                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                        <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                        Apply
                    </button>
                    @if(request()->hasAny(['q', 'type', 'sort', 'direction']))
                        <a href="{{ route('companies.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-raised border border-line text-xs font-semibold text-ink shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    {{ $companies->total() }} {{ Str::plural('Partner', $companies->total()) }} Connected
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Company & Location</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Specialization</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Key Contacts</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Status</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeff3]">
                    @forelse($companies as $index => $company)
                        <tr class="group transition-all duration-150 hover:bg-[#fafaf3]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($companies->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Company Name & Address -->
                            <td>
                                <div class="flex items-center gap-3.5 py-1">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface text-ink shadow-2xs group-hover:border-accent-line group-hover:shadow-[0_4px_12px_rgba(213,255,39,0.2)] group-hover:text-accent transition duration-150">
                                        <x-icon name="building" class="h-5 w-5"/>
                                    </div>
                                    <div>
                                        <a class="font-bold text-ink text-sm tracking-tight hover:text-accent transition inline-flex items-center gap-1.5" href="{{ route('companies.show', $company) }}">
                                            {{ $company->name }}
                                        </a>
                                        <p class="text-xs text-muted mt-0.5 line-clamp-1 max-w-sm">{{ $company->address ?: 'No address specified' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Specialization -->
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($company->is_knitting)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold bg-[#d5ff27]/20 border border-[#ccd994] text-[#222800] shadow-2xs">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#526500]"></span> Circular Knitting
                                        </span>
                                    @endif
                                    @if($company->is_dyeing)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold bg-purple-50 border border-purple-200 text-purple-900 shadow-2xs">
                                            <span class="h-1.5 w-1.5 rounded-full bg-purple-600"></span> Dyeing & Finishing
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Contacts -->
                            <td>
                                <div class="text-xs space-y-1">
                                    @forelse($company->contacts as $contact)
                                        <div class="font-medium text-ink flex items-center gap-1.5">
                                            <span>{{ $contact->name }}</span>
                                            <span class="text-[11px] text-muted font-normal">({{ str_replace('_', ' ', $contact->role) }})</span>
                                        </div>
                                    @empty
                                        <span class="text-muted text-xs">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($company->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-lime-50 text-lime-800 border border-lime-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-lime-600"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="number whitespace-nowrap pr-6">
                                <div class="inline-flex items-center gap-1.5">
                                    <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent shadow-2xs transition" href="{{ route('companies.show', $company) }}">
                                        View
                                    </a>
                                    <a class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-line bg-surface text-muted hover:text-ink hover:border-muted transition shadow-2xs" href="{{ route('companies.edit', $company) }}" title="Edit company">
                                        <x-icon name="pen" class="h-3.5 w-3.5"/>
                                    </a>
                                    <x-delete-button :action="route('companies.destroy', $company)" label="Delete company" icon="trash" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty py-20 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-soft text-accent-ink mb-4 shadow-sm">
                                    <x-icon name="building" class="h-8 w-8"/>
                                </div>
                                <h3 class="text-lg font-bold text-ink">No companies registered</h3>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">Add your partner circular knitting mills and fabric dyeing houses to start managing production lots.</p>
                                <div class="mt-5">
                                    <a href="{{ route('companies.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                                        <x-icon name="plus" class="h-4 w-4"/>
                                        Add First Company
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-line/60 bg-raised/30">
            {{ $companies->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
