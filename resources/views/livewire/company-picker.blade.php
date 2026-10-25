<div>
    <div class="flex flex-wrap items-end gap-3"><label class="field flex-1">Search company<input type="text" wire:model.live.debounce.250ms="search" placeholder="Type a company name"></label><button type="button" class="btn btn-secondary" wire:click="$set('open',true)"><x-icon name="plus"/>Add company</button></div>
    <label class="field mt-3">Selected {{ $type }} company<select wire:model.live="selected" name="company_id" required><option value="">Choose company</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}{{ !$company->is_active ? ' (inactive)' : '' }}</option>@endforeach</select></label>
    @if($open)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-5" role="dialog" aria-modal="true" aria-labelledby="company-modal-title" x-data @keydown.escape.prevent="$wire.set('open',false)" @keydown.enter.prevent="$wire.createCompany()">
        <div class="panel max-h-[85vh] w-full max-w-lg overflow-auto p-6">
            <div class="mb-5 flex items-center justify-between"><h2 id="company-modal-title">Add {{ $type }} company</h2><button type="button" wire:click="$set('open',false)" aria-label="Close modal">✕</button></div>
            <div class="space-y-4"><label class="field">Company name<input wire:model="name" type="text"></label>@error('name')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            <label class="field">Address<textarea wire:model="address" rows="2"></textarea></label>@error('address')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            @foreach($type==='knitting' ? ['manager','operator'] : ['dyeing_master'] as $role)
                <div class="grid grid-cols-2 gap-4"><label class="field">{{ ucwords(str_replace('_',' ',$role)) }} name<input type="text" wire:model="contacts.{{ $role }}.name"></label><label class="field">Phone number<input type="text" wire:model="contacts.{{ $role }}.phone"></label></div>
                @error('contacts.'.$role.'.name')<p class="text-xs text-danger">{{ $message }}</p>@enderror @error('contacts.'.$role.'.phone')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            @endforeach
            <div class="mt-5 flex justify-end gap-3"><button type="button" class="btn btn-secondary" wire:click="$set('open',false)">Cancel</button><button type="button" class="btn btn-primary" wire:click="createCompany" wire:loading.attr="disabled">Save company</button></div>
            </div>
        </div>
    </div>
    @endif
</div>
