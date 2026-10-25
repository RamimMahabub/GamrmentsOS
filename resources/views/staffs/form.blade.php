<x-app-layout><x-slot name="header"><x-page-heading :title="$staff->exists ? 'Edit staff' : 'Add staff'"/></x-slot>
<form class="panel space-y-5 p-6 max-w-3xl" method="POST" action="{{ $staff->exists ? route('staffs.update',$staff) : route('staffs.store') }}">@csrf @if($staff->exists) @method('PUT') @endif
@foreach(['name'=>'Name','phone'=>'Phone','role'=>'Role'] as $key=>$label)<label class="field">{{ $label }}<input required name="{{ $key }}" value="{{ old($key,$staff->$key ?? ($key==='role' ? 'cutting_man' : '')) }}"></label>@endforeach
<label class="field">Address<textarea name="address">{{ old('address',$staff->address) }}</textarea></label>
<input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$staff->is_active ?? true))> Active</label>
<div><button class="btn btn-primary">Save staff</button> <a class="btn btn-secondary" href="{{ route('staffs.index') }}">Cancel</a></div></form></x-app-layout>