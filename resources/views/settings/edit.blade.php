<x-app-layout>
<x-slot name="header">
    <x-page-heading title="Workspace settings" subtitle="Set your preferred units, DIA values, production rates, and color names.">
        <a class="btn btn-secondary" href="{{ route('profile.edit') }}">Account settings</a>
    </x-page-heading>
</x-slot>

<form class="panel mb-7 max-w-4xl" method="POST" action="{{ route('settings.update') }}" x-data="{presets:@js(old('dia_presets',\App\Models\Setting::getValue('dia_presets',[32,34,36,40])))}">
    @csrf
    @method('PUT')
    
    <div class="panel-head">
        <h2>Units &amp; DIA presets</h2>
    </div>
    
    <div class="grid gap-6 p-6 sm:grid-cols-2">
        <label class="field">
            <span>Default weight unit</span>
            <select name="default_weight_unit">
                @foreach(['lb'=>'Pound (lb)','kg'=>'Kilogram (kg)'] as $unit=>$label)
                    <option value="{{ $unit }}" @selected(old('default_weight_unit',$defaultUnit)===$unit)>{{ $label }}</option>
                @endforeach
            </select>
            <span class="hint">Yarn entry and display unit. Fabric stays in kg.</span>
        </label>
        
        <label class="field">
            <span>Price currency label</span>
            <input required type="text" name="currency_label" value="{{ old('currency_label',$currency) }}" maxlength="10">
        </label>

        <div class="field sm:col-span-2">
            <label>DIA presets</label>
            <div class="flex flex-wrap gap-3">
                <template x-for="(preset,i) in presets" :key="i">
                    <div class="flex items-center gap-1">
                        <input type="number" min="1" max="1000" required x-model="presets[i]" name="dia_presets[]" class="w-20" :aria-label="'DIA preset '+(i+1)">
                        <button type="button" @click="presets.splice(i,1)" class="px-2 text-muted" :aria-label="'Remove DIA preset '+preset">✕</button>
                    </div>
                </template>
                <button type="button" class="btn btn-secondary" @click="presets.push('')"><x-icon name="plus"/>Add DIA</button>
            </div>
        </div>
    </div>

    <!-- Production & Costing Default Rates -->
    <div class="panel-head border-t border-line">
        <h2>Production Rates &amp; Costing Defaults</h2>
        <span class="hint">These defaults pre-fill and calculate lot production costs when specific rates are not snapshotted.</span>
    </div>

    <div class="p-6 space-y-6">
        <!-- Mill Rates -->
        <div>
            <h3 class="text-sm font-semibold text-ink mb-3">Mill Partner Fallback Rates (per kg)</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="field">
                    <span>Default Knitting Rate / kg ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="default_knitting_rate_per_kg" value="{{ old('default_knitting_rate_per_kg', $knittingRate) }}">
                    <span class="hint">Applies if knitting company has no specific rate</span>
                </label>
                <label class="field">
                    <span>Default Dyeing Rate / kg ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="default_dyeing_rate_per_kg" value="{{ old('default_dyeing_rate_per_kg', $dyeingRate) }}">
                    <span class="hint">Applies if dyeing company has no specific rate</span>
                </label>
            </div>
        </div>

        <!-- Cutting Rates per dozen -->
        <div class="border-t border-line pt-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Cutting Labor Rates (per dozen)</h3>
            <div class="grid gap-4 sm:grid-cols-4">
                <label class="field">
                    <span>Body Cutting / doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="cutting_rates[body]" value="{{ old('cutting_rates.body', $cuttingRates['body'] ?? '0') }}">
                </label>
                <label class="field">
                    <span>Underpants Cutting / doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="cutting_rates[cutting]" value="{{ old('cutting_rates.cutting', $cuttingRates['cutting'] ?? ($cuttingRates['body'] ?? '0')) }}">
                </label>
                <label class="field">
                    <span>Neck Cutting / doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="cutting_rates[neck]" value="{{ old('cutting_rates.neck', $cuttingRates['neck'] ?? '0') }}">
                </label>
                <label class="field">
                    <span>Rib Cutting / doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="cutting_rates[rib]" value="{{ old('cutting_rates.rib', $cuttingRates['rib'] ?? '0') }}">
                </label>
            </div>
        </div>

        <!-- Sewing Station Rates per dozen -->
        <div class="border-t border-line pt-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Sewing Station Labor Rates (per dozen)</h3>
            <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
                @php
                    $stations = [
                        'fold' => 'Fold',
                        'lock' => 'Lock',
                        'down' => 'Down',
                        'neck_join' => 'Neck Join',
                        'neck_finishing' => 'Neck Finishing',
                        'qc_phase_1' => 'QC Phase 1',
                        'iron' => 'Iron',
                        'qc_phase_2' => 'QC Phase 2',
                    ];
                @endphp
                @foreach($stations as $sKey => $sLabel)
                    <label class="field">
                        <span>{{ $sLabel }} ({{ $currency }})</span>
                        <input type="number" step="0.01" min="0" name="sewing_rates[{{ $sKey }}]" value="{{ old('sewing_rates.'.$sKey, $sewingRates[$sKey] ?? '0') }}">
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Underpants Sewing Workflow Sub-steps Configuration -->
        <div class="border-t border-line pt-5" x-data="{
            groups: @js($underpantsSteps),
            addStep(group) {
                if (!this.groups[group]) this.groups[group] = [];
                if (this.groups[group].length >= 8) return;
                this.groups[group].push({
                    key: group + '_' + (this.groups[group].length + 1),
                    label: 'Sub-step ' + (this.groups[group].length + 1),
                    rate: '10.00'
                });
            },
            removeStep(group, idx) {
                this.groups[group].splice(idx, 1);
            },
            moveStep(group, fromIdx, toIdx) {
                if (toIdx < 0 || toIdx >= this.groups[group].length) return;
                const item = this.groups[group].splice(fromIdx, 1)[0];
                this.groups[group].splice(toIdx, 0, item);
            }
        }">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Underpants (Boxer &amp; Underwear) Sewing Stations &amp; Sub-steps</h3>
                    <p class="text-xs text-muted">Configure 1 to 8 customizable sub-steps and labor rate per dozen for Over Lock, Lock, and Elastic Join.</p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Group 1: Over Lock -->
                <div class="rounded-xl border border-line bg-raised/40 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-xs uppercase text-ink">1. Over Lock</span>
                        <button type="button" class="btn btn-secondary btn-xs" @click="addStep('over_lock')" :disabled="groups.over_lock?.length >= 8">+ Add (max 8)</button>
                    </div>
                    <div class="space-y-2 mt-3">
                        <template x-for="(step, idx) in (groups.over_lock || [])" :key="idx">
                            <div class="bg-surface p-2.5 rounded-lg border border-line space-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] text-muted w-4" x-text="idx + 1"></span>
                                    <input type="hidden" :name="'underpants_sewing_steps[over_lock]['+idx+'][key]'" :value="step.key">
                                    <input type="text" required class="text-xs py-1 px-2 border border-line rounded flex-1" :name="'underpants_sewing_steps[over_lock]['+idx+'][label]'" x-model="step.label" placeholder="e.g. Crotch Join">
                                    <div class="flex flex-col">
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('over_lock', idx, idx - 1)" :disabled="idx === 0">▲</button>
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('over_lock', idx, idx + 1)" :disabled="idx === groups.over_lock.length - 1">▼</button>
                                    </div>
                                    <button type="button" class="text-xs text-danger hover:text-red-700 px-1" @click="removeStep('over_lock', idx)">&times;</button>
                                </div>
                                <div class="flex items-center justify-between gap-2 pl-5 pt-1 border-t border-line/60">
                                    <span class="text-[11px] text-muted font-medium">Rate / doz ({{ $currency }})</span>
                                    <input type="number" step="0.01" min="0" class="text-xs py-0.5 px-2 w-24 text-right border border-line rounded" :name="'underpants_sewing_steps[over_lock]['+idx+'][rate]'" x-model="step.rate" placeholder="0.00">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Group 2: Lock -->
                <div class="rounded-xl border border-line bg-raised/40 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-xs uppercase text-ink">2. Lock</span>
                        <button type="button" class="btn btn-secondary btn-xs" @click="addStep('lock')" :disabled="groups.lock?.length >= 8">+ Add (max 8)</button>
                    </div>
                    <div class="space-y-2 mt-3">
                        <template x-for="(step, idx) in (groups.lock || [])" :key="idx">
                            <div class="bg-surface p-2.5 rounded-lg border border-line space-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] text-muted w-4" x-text="idx + 1"></span>
                                    <input type="hidden" :name="'underpants_sewing_steps[lock]['+idx+'][key]'" :value="step.key">
                                    <input type="text" required class="text-xs py-1 px-2 border border-line rounded flex-1" :name="'underpants_sewing_steps[lock]['+idx+'][label]'" x-model="step.label" placeholder="e.g. Fly Lock">
                                    <div class="flex flex-col">
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('lock', idx, idx - 1)" :disabled="idx === 0">▲</button>
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('lock', idx, idx + 1)" :disabled="idx === groups.lock.length - 1">▼</button>
                                    </div>
                                    <button type="button" class="text-xs text-danger hover:text-red-700 px-1" @click="removeStep('lock', idx)">&times;</button>
                                </div>
                                <div class="flex items-center justify-between gap-2 pl-5 pt-1 border-t border-line/60">
                                    <span class="text-[11px] text-muted font-medium">Rate / doz ({{ $currency }})</span>
                                    <input type="number" step="0.01" min="0" class="text-xs py-0.5 px-2 w-24 text-right border border-line rounded" :name="'underpants_sewing_steps[lock]['+idx+'][rate]'" x-model="step.rate" placeholder="0.00">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Group 3: Elastic Join -->
                <div class="rounded-xl border border-line bg-raised/40 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-xs uppercase text-ink">3. Elastic Join</span>
                        <button type="button" class="btn btn-secondary btn-xs" @click="addStep('elastic_join')" :disabled="groups.elastic_join?.length >= 8">+ Add (max 8)</button>
                    </div>
                    <div class="space-y-2 mt-3">
                        <template x-for="(step, idx) in (groups.elastic_join || [])" :key="idx">
                            <div class="bg-surface p-2.5 rounded-lg border border-line space-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] text-muted w-4" x-text="idx + 1"></span>
                                    <input type="hidden" :name="'underpants_sewing_steps[elastic_join]['+idx+'][key]'" :value="step.key">
                                    <input type="text" required class="text-xs py-1 px-2 border border-line rounded flex-1" :name="'underpants_sewing_steps[elastic_join]['+idx+'][label]'" x-model="step.label" placeholder="e.g. Waistband Elastic">
                                    <div class="flex flex-col">
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('elastic_join', idx, idx - 1)" :disabled="idx === 0">▲</button>
                                        <button type="button" class="text-[10px] text-muted hover:text-ink leading-none px-1" @click="moveStep('elastic_join', idx, idx + 1)" :disabled="idx === groups.elastic_join.length - 1">▼</button>
                                    </div>
                                    <button type="button" class="text-xs text-danger hover:text-red-700 px-1" @click="removeStep('elastic_join', idx)">&times;</button>
                                </div>
                                <div class="flex items-center justify-between gap-2 pl-5 pt-1 border-t border-line/60">
                                    <span class="text-[11px] text-muted font-medium">Rate / doz ({{ $currency }})</span>
                                    <input type="number" step="0.01" min="0" class="text-xs py-0.5 px-2 w-24 text-right border border-line rounded" :name="'underpants_sewing_steps[elastic_join]['+idx+'][rate]'" x-model="step.rate" placeholder="0.00">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Trim & Logistics -->
        <div class="border-t border-line pt-5">
            <h3 class="text-sm font-semibold text-ink mb-3">Direct Trim &amp; Logistics Standard Allowances</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="field">
                    <span>Sewing Thread Cost / finished doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="thread_cost_per_dozen" value="{{ old('thread_cost_per_dozen', $threadCost) }}">
                    <span class="hint">Standard thread allowance per dozen</span>
                </label>
                <label class="field">
                    <span>Carrying / Transport / finished doz ({{ $currency }})</span>
                    <input type="number" step="0.01" min="0" name="carrying_cost_per_dozen" value="{{ old('carrying_cost_per_dozen', $carryingCost) }}">
                    <span class="hint">Factory carrying &amp; mill logistics per dozen</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-end border-t border-line px-6 py-4">
        <button class="btn btn-primary">Save preferences &amp; Rates</button>
    </div>
</form>

<div class="panel">
    <div class="panel-head">
        <h2>Colors</h2>
        <span class="hint">New colors are created from dyeing forms</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>HEX</th>
                    <th>RENAME / MERGE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colors as $color)
                    <tr>
                        <td class="font-medium">{{ $color->name }}</td>
                        <td>{{ $color->hex ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('settings.color',$color) }}" class="flex flex-wrap items-center gap-2" x-data @submit="if($el.target_id.value && !confirm('Merge this color into the selected color?')) $event.preventDefault()">
                                @csrf
                                @method('PUT')
                                <input type="text" required name="name" value="{{ $color->name }}" aria-label="Rename {{ $color->name }}">
                                <input type="text" name="hex" value="{{ $color->hex }}" placeholder="#B8A15E" aria-label="Hex for {{ $color->name }}" style="min-width:100px;max-width:110px">
                                <select name="target_id" aria-label="Merge {{ $color->name }} into">
                                    <option value="">Rename / set hex</option>
                                    @foreach($colorOptions->where('id','!=',$color->id) as $target)
                                        <option value="{{ $target->id }}">Merge into {{ $target->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-secondary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">Colors will appear after your first dyeing job.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-5">{{ $colors->links('components.pagination') }}</div>
</div>
</x-app-layout>
