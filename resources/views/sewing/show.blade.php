<x-app-layout>
    <x-slot name="header"><x-page-heading :title="'Sewing execution · '.$job->sewing_date->format('d M Y')" :subtitle="$job->staff->name"><div class="flex gap-2"><a class="btn btn-secondary" href="{{ route('sewing-jobs.edit', $job) }}">Edit</a><a class="btn btn-secondary" href="{{ route('sewing-jobs.index') }}">Back</a></div></x-page-heading></x-slot>
    <section class="panel overflow-hidden">
        <div class="panel-head">
            <div>
                <h2>{{ strtoupper(str_replace('_', ' ', $job->current_step)) }} handoff</h2>
                <p class="hint mt-1">This record holds batches released from one sewing station. The next station can receive them after this handoff is saved.</p>
            </div>
            <span class="pill">{{ $job->rows->count() }} garment batches</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lot / Color / DIA</th>
                        <th>Components</th>
                        @if(in_array($job->current_step, ['qc_phase_1', 'qc_phase_2'], true))
                            <th class="number text-emerald-700">Passed</th>
                            <th class="number text-blue-700">Altered</th>
                            <th class="number text-rose-700">Rejected</th>
                        @elseif($job->current_step === 'iron')
                            <th class="number">Ironed quantity</th>
                        @else
                            <th class="number">Completed</th>
                            @if($job->current_step === 'neck_finishing')
                                <th class="number">Rejected pcs</th>
                                <th class="number">Accepted pcs</th>
                            @endif
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($job->rows as $row)
                        @php($step = $job->current_step)
                        <tr>
                            <td>
                                <a class="link font-bold" href="{{ route('lots.show', $row->lot) }}">{{ $row->lot->lot_number }}</a><br>
                                <span class="hint">{{ $row->color }} · DIA {{ $row->dia }}</span>
                            </td>
                            <td>
                                <span class="hint">Body #{{ $row->source_body_row_id }} · Neck #{{ $row->source_neck_row_id }}</span>
                            </td>
                            @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                                <td class="number font-semibold text-emerald-700">
                                    {{ $row->{$step.'_dozens'} }} doz {{ $row->{$step.'_pieces'} }} pc
                                    <span class="hint block text-[11px]">({{ (int) $row->{$step.'_dozens'} * 12 + (int) $row->{$step.'_pieces'} }} pcs)</span>
                                </td>
                                <td class="number text-blue-700 font-medium">
                                    {{ $row->{$step.'_altered_pieces'} }} pcs
                                </td>
                                <td class="number text-rose-700 font-medium">
                                    {{ $row->{$step.'_rejected_pieces'} }} pcs
                                </td>
                            @elseif($step === 'iron')
                                <td class="number font-semibold">
                                    {{ $row->iron_dozens }} doz {{ $row->iron_pieces }} pc
                                    <span class="hint block text-[11px]">({{ (int) $row->iron_dozens * 12 + (int) $row->iron_pieces }} pcs)</span>
                                </td>
                            @else
                                <td class="number">
                                    {{ $row->{$step.'_dozens'} }} doz {{ $row->{$step.'_pieces'} }} pc
                                </td>
                                @if($step === 'neck_finishing')
                                    <td class="number text-rose-700">{{ $row->rejected_pieces }} pcs</td>
                                    <td class="number font-semibold">{{ \App\Support\Decimal::scale(\App\Support\Decimal::of($row->neck_finishing_dozens)->multipliedBy(12)->plus($row->neck_finishing_pieces)->minus($row->rejected_pieces)) }} pcs</td>
                                @endif
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
