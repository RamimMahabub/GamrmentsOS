<x-app-layout><x-slot name="header"><x-page-heading :title="$staff->name"><a class="btn btn-secondary" href="{{ route('staffs.edit',$staff) }}">Edit staff</a></x-page-heading></x-slot>
<p class="mb-5">{{ $staff->phone }} ? {{ $staff->address }} ? {{ $staff->is_active ? 'Active' : 'Inactive' }}</p>
<form method="GET" class="panel flex flex-wrap gap-4 p-5 mb-6"><label>From <input type="date" name="from" value="{{ $from }}"></label><label>To <input type="date" name="to" value="{{ $to }}"></label><button class="btn btn-secondary">Filter</button></form>
<div class="panel p-6"><h2>Productivity</h2>@foreach(['body','neck','rib'] as $section)<p>{{ ucfirst($section) }}: {{ $totals['cutting:'.$section]['dozens'] ?? '0.000' }} dozen / {{ $totals['cutting:'.$section]['pieces'] ?? '0.000' }} pieces</p>@endforeach
<p>Grand total: {{ \App\Support\Decimal::scale(\App\Support\Decimal::sum($entries->pluck('dozens'))) }} dozen / {{ \App\Support\Decimal::scale(\App\Support\Decimal::sum($entries->pluck('pieces'))) }} pieces</p><p class="hint mt-2">Productivity measures cutting work. The same pieces count as work at body cutting and again at neck cutting.</p></div>
@if(view()->exists('staffs.cutting-entries')) @include('staffs.cutting-entries') @else <p class="mt-6">No cutting entries in this period.</p> @endif
</x-app-layout>
