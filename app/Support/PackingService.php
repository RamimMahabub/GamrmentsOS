<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\PackingJob;
use App\Models\PackingJobRow;
use App\Models\PackingMaterial;
use App\Models\Setting;
use App\Models\SewingJobRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackingService
{
    public const DEFAULT_DIA_SIZE_MAPPING = [
        18 => 'S',
        20 => 'M',
        22 => 'L',
        24 => 'XL',
        26 => '2XL',
        28 => '3XL',
        30 => '4XL',
        32 => '5XL',
        34 => '6XL',
        36 => '7XL',
    ];

    public function __construct(
        public PackingStockLedger $ledger
    ) {}

    public function getMappedSize(int|string $dia): string
    {
        $mapping = Setting::getValue('dia_size_mapping', self::DEFAULT_DIA_SIZE_MAPPING);

        return $mapping[(string) $dia] ?? $mapping[(int) $dia] ?? "DIA {$dia}";
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getAvailableBatches(?PackingJob $currentJob = null): Collection
    {
        $currentJobId = $currentJob?->id;

        // Group sewing rows to find passed pieces after Iron / QC Phase 2
        $qc2Rows = SewingJobRow::query()
            ->selectRaw('group_key, SUM(qc_phase_2_dozens * 12 + qc_phase_2_pieces) AS passed_pieces')
            ->groupBy('group_key')
            ->pluck('passed_pieces', 'group_key');

        $ironRows = SewingJobRow::query()
            ->selectRaw('group_key, SUM(iron_dozens * 12 + iron_pieces) AS iron_pieces')
            ->groupBy('group_key')
            ->pluck('iron_pieces', 'group_key');

        $packedCounts = PackingJobRow::query()
            ->when($currentJobId, fn ($q) => $q->where('packing_job_id', '!=', $currentJobId))
            ->selectRaw('group_key, SUM(total_pieces) AS packed_pieces')
            ->groupBy('group_key')
            ->pluck('packed_pieces', 'group_key');

        return CuttingJobRoll::with(['job', 'lot'])
            ->whereIn('cutting_type', ['body', 'cutting', 'neck'])
            ->whereHas('job')
            ->get()
            ->groupBy(fn (CuttingJobRoll $row): string => $row->lot_id.':'.$row->color.':'.$row->dia)
            ->map(function (Collection $group, string $key) use ($qc2Rows, $ironRows, $packedCounts, $currentJob): ?array {
                $body = $group->firstWhere('cutting_type', 'cutting') ?? $group->firstWhere('cutting_type', 'body');
                if (! $body) {
                    return null;
                }

                // If QC Phase 2 was run for this batch, use its passed pieces.
                // Otherwise fall back to Iron pieces.
                $hasQc2 = isset($qc2Rows[$key]) && (int) $qc2Rows[$key] > 0;
                $capacityPieces = $hasQc2
                    ? (int) $qc2Rows[$key]
                    : (int) ($ironRows[$key] ?? 0);

                if ($capacityPieces <= 0) {
                    return null;
                }

                $alreadyPacked = (int) ($packedCounts[$key] ?? 0);
                $remainingPieces = $capacityPieces - $alreadyPacked;

                // If currently editing, we still want to show the batch even if remaining is 0
                $isCurrentlyInJob = $currentJob && $currentJob->rows->contains('group_key', $key);

                if ($remainingPieces <= 0 && ! $isCurrentlyInJob) {
                    return null;
                }

                $availDozens = intdiv($remainingPieces, 12);
                $availPiecesRemainder = $remainingPieces % 12;
                $suggestedSize = $this->getMappedSize($body->dia);

                return [
                    'id' => $key,
                    'group_key' => $key,
                    'lot_id' => $body->lot_id,
                    'lot' => $body->lot->lot_number,
                    'color' => $body->color,
                    'dia' => $body->dia,
                    'suggested_size' => $suggestedSize,
                    'capacity_pieces' => $capacityPieces,
                    'already_packed' => $alreadyPacked,
                    'total_pieces' => $remainingPieces,
                    'dozens' => $availDozens,
                    'pieces' => $availPiecesRemainder,
                    'source_step' => $hasQc2 ? 'QC Phase 2' : 'Iron',
                ];
            })
            ->filter()
            ->values();
    }

    public function save(array $data, ?PackingJob $job = null): PackingJob
    {
        return DB::transaction(function () use ($data, $job): PackingJob {
            $job ??= new PackingJob;
            $isEditing = $job->exists;

            if ($isEditing) {
                // Reverse previously allocated stock
                $this->ledger->reverseJob($job, $data['packing_date']);
                $job->rows()->delete();
            }

            $job->fill([
                'packing_date' => $data['packing_date'],
                'staff_id' => $data['staff_id'],
                'notes' => $data['notes'] ?? null,
            ])->save();

            $availableBatches = $this->getAvailableBatches($job)->keyBy('group_key');

            $sizePieceCounts = [];
            $totalPiecesAllRows = 0;
            $totalPacketsAllRows = 0;
            $totalPacketMaterialsCount = 0;
            $lotIds = [];

            foreach ($data['rows'] as $row) {
                $groupKey = $row['group_key'];
                [$lotId, $color, $dia] = explode(':', $groupKey, 3);
                $lotId = (int) $lotId;
                $dia = (int) $dia;
                $lotIds[] = $lotId;

                $batchMeta = $availableBatches->get($groupKey);
                $remainingAvailable = $batchMeta['total_pieces'] ?? 0;

                $dozens = Decimal::of($row['dozens'] ?? 0);
                $pieces = (int) ($row['pieces'] ?? 0);
                $totalPieces = (int) $dozens->multipliedBy(12)->plus($pieces)->toInt();
                $rejectedPieces = (int) ($row['rejected_pieces'] ?? 0);

                if ($totalPieces <= 0 && $rejectedPieces <= 0) {
                    continue;
                }

                if ($totalPieces > $remainingAvailable) {
                    throw ValidationException::withMessages([
                        'rows' => "The packed quantity ({$totalPieces} pcs) for batch {$groupKey} exceeds the available quantity ({$remainingAvailable} pcs).",
                    ]);
                }

                // 6 pcs per packet, 2 packets per dozen
                $packets = intdiv($totalPieces, 6);
                $loosePieces = $totalPieces % 6;
                $packetMaterials = $packets + ($loosePieces > 0 ? 1 : 0);

                $size = ! empty($row['size']) ? (string) $row['size'] : $this->getMappedSize($dia);

                $job->rows()->create([
                    'lot_id' => $lotId,
                    'group_key' => $groupKey,
                    'color' => $color,
                    'dia' => $dia,
                    'size' => $size,
                    'dozens' => intdiv($totalPieces, 12),
                    'packets' => $packets,
                    'pieces' => $loosePieces,
                    'total_pieces' => $totalPieces,
                    'rejected_pieces' => $rejectedPieces,
                ]);

                $sizePieceCounts[$size] = ($sizePieceCounts[$size] ?? 0) + $totalPieces;
                $totalPiecesAllRows += $totalPieces;
                $totalPacketsAllRows += $packets;
                $totalPacketMaterialsCount += $packetMaterials;
            }

            // Write stock consumption movements to ledger
            if ($totalPiecesAllRows > 0) {
                $date = $job->packing_date->toDateString();

                // 1. Poly Bag (1 per piece)
                $polyBag = PackingMaterial::where('category', 'poly_bag')->where('is_active', true)->first();
                if ($polyBag) {
                    $this->ledger->write(
                        $polyBag,
                        'packing_consumption',
                        (string) Decimal::of($totalPiecesAllRows)->negated(),
                        $date,
                        $job,
                        note: "Packed {$totalPiecesAllRows} pieces"
                    );
                }

                // 2. Size Stickers (1 per piece for each size)
                foreach ($sizePieceCounts as $sizeCode => $count) {
                    if ($count <= 0) {
                        continue;
                    }
                    $sizeSticker = PackingMaterial::where('category', 'size_sticker')
                        ->where('size_code', $sizeCode)
                        ->where('is_active', true)
                        ->first() ?? PackingMaterial::where('category', 'size_sticker')->where('is_active', true)->first();

                    if ($sizeSticker) {
                        $this->ledger->write(
                            $sizeSticker,
                            'packing_consumption',
                            (string) Decimal::of($count)->negated(),
                            $date,
                            $job,
                            note: "Size {$sizeCode} sticker for {$count} pieces"
                        );
                    }
                }

                // 3. Other Sticker(s) (1 per piece for each active other sticker)
                $otherStickers = PackingMaterial::where('category', 'other_sticker')->where('is_active', true)->get();
                foreach ($otherStickers as $sticker) {
                    $this->ledger->write(
                        $sticker,
                        'packing_consumption',
                        (string) Decimal::of($totalPiecesAllRows)->negated(),
                        $date,
                        $job,
                        note: "Sticker for {$totalPiecesAllRows} pieces"
                    );
                }

                // 4. Packet Material (1 per packet, including 1 for partial packet if loose pieces exist)
                if ($totalPacketMaterialsCount > 0) {
                    $packetMaterial = PackingMaterial::where('category', 'packet_material')->where('is_active', true)->first();
                    if ($packetMaterial) {
                        $this->ledger->write(
                            $packetMaterial,
                            'packing_consumption',
                            (string) Decimal::of($totalPacketMaterialsCount)->negated(),
                            $date,
                            $job,
                            note: "Packet material for {$totalPacketsAllRows} full packets and loose pieces ({$totalPacketMaterialsCount} total)"
                        );
                    }
                }
            }

            Lot::syncCurrentStageFor(array_unique($lotIds));

            return $job;
        });
    }

    public function delete(PackingJob $job): void
    {
        DB::transaction(function () use ($job): void {
            $lotIds = $job->rows()->pluck('lot_id')->unique()->all();
            $this->ledger->reverseJob($job, now()->toDateString());
            $job->rows()->delete();
            $job->delete();
            Lot::syncCurrentStageFor($lotIds);
        });
    }
}
