<x-app-layout>
<x-slot name="header"><x-page-heading :title="$job->lot->lot_number.' · Dyeing'" :subtitle="'Entry '.$job->entry_date->format('d/m/Y').' · Return '.($job->return_date?->format('d/m/Y') ?? 'pending')"><a class="btn btn-secondary" href="{{ route('dyeing-jobs.edit',$job) }}">Edit dyeing job</a><a class="btn btn-primary" href="{{ route('lots.show',$job->lot) }}">Lot trace ↗</a></x-page-heading></x-slot>
<div class="mb-6"><x-company-contacts :company="$job->company"/></div><x-dyeing-tables :job="$job"/>
@if($job->notes)<div class="panel mt-6 p-6"><h2 class="mb-3">Dyeing notes</h2><p class="whitespace-pre-line text-muted">{{ $job->notes }}</p></div>@endif
</x-app-layout>
