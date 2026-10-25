@props(['action','label'=>'Delete'])
<form method="POST" action="{{ $action }}" class="inline" x-data @submit="if (!confirm('Delete this record? This action will preserve its audit history.')) $event.preventDefault()">@csrf @method('DELETE')<button class="text-xs font-medium text-danger hover:underline">{{ $label }}</button></form>
