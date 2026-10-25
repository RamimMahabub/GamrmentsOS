<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\Staff;
use App\Models\Yarn;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Knitting\KnittingService;
use App\Modules\Yarn\StockLedger;
use App\Support\CuttingTotals;
use App\Support\Decimal;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

#[Group('concurrency')]
class ConcurrencyTest extends TestCase
{
    use ManufacturingFixtures;

    private bool $prepared = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Concurrency checks require InnoDB. Run with phpunit.mysql.xml.');
        }
        $database = config('database.connections.'.config('database.default').'.database');
        if (! str_ends_with($database, '_testing')) {
            throw new \RuntimeException('Concurrency tests require a dedicated database ending in _testing.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->prepared = true;
    }

    protected function tearDown(): void
    {
        if ($this->prepared) {
            $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        }
        parent::tearDown();
    }

    private function race(string $mode, array $payloads): array
    {
        $gate = storage_path('framework/testing-'.bin2hex(random_bytes(8)));
        $processes = [];
        $connection = config('database.connections.'.config('database.default'));
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => config('database.default'), 'DB_HOST' => $connection['host'], 'DB_PORT' => (string) $connection['port'],
            'DB_DATABASE' => $connection['database'], 'DB_USERNAME' => $connection['username'], 'DB_PASSWORD' => $connection['password'], 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync'];
        try {
            foreach ($payloads as $payload) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/concurrent-worker.php'), $mode, base64_encode(json_encode($payload)), $gate], base_path(), $environment);
                $process->setTimeout(45);
                $process->start();
                $processes[] = $process;
            }
            touch($gate);
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertContains($process->getExitCode(), [0, 2], $process->getErrorOutput().$process->getOutput());
                $results[] = ['code' => $process->getExitCode(), 'result' => json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR)];
            }

            return $results;
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            if (is_file($gate)) {
                unlink($gate);
            }
        }
    }

    public function test_simultaneous_lot_creation_produces_four_unique_numbers(): void
    {
        $yarn = Yarn::factory()->create(['name' => 'Sudan']);
        $results = $this->race('lot', array_fill(0, 4, ['yarn_id' => $yarn->id, 'date' => '2026-05-10']));
        $numbers = collect($results)->pluck('result.lot_number')->sort()->values()->all();
        $this->assertSame(['SU-100526', 'SU-100526-2', 'SU-100526-3', 'SU-100526-4'], $numbers);
        $this->assertDatabaseCount('lots', 4);
    }

    public function test_simultaneous_knitting_cannot_take_stock_negative(): void
    {
        $yarn = Yarn::factory()->withStock('400.000')->create();
        $company = Company::factory()->create();
        $results = $this->race('knitting', array_fill(0, 4, $this->knittingData($yarn, $company, '200.000', [32])));
        $this->assertCount(2, array_filter($results, fn ($result): bool => $result['code'] === 0));
        $this->assertSame('0.000', app(StockLedger::class)->balance($yarn));
        $this->assertDatabaseCount('knitting_jobs', 2);
    }

    public function test_simultaneous_dyeing_cannot_allocate_the_same_fabric_twice(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company, '200.000', [32]));
        $results = $this->race('dyeing', array_fill(0, 4, $this->dyeingData($job->lot_id, $company, '150.000', 1, null, [32])));
        $this->assertCount(1, array_filter($results, fn ($result): bool => $result['code'] === 0));
        $this->assertDatabaseCount('dyeing_jobs', 1);
        $this->assertSame('50.000', app(FabricAvailability::class)->forLot($job->lot->fresh())[0]['available_kg']);
    }

    public function test_simultaneous_neck_jobs_cannot_exceed_the_body_dozen_balance(): void
    {
        $job = CuttingJob::factory()->create(['cutting_type' => 'body', 'cutting_date' => '2026-10-20']);
        $body = CuttingJobRoll::factory()->create(['cutting_job_id' => $job->id, 'dozens' => '10', 'cut_weight_kg' => '5']);
        $payloads = Staff::factory()->count(4)->create()->map(fn (Staff $staff): array => [
            'staff_id' => $staff->id,
            'cutting_date' => '2026-10-23',
            'rows' => [['source_body_row_id' => $body->id, 'dozens' => '3', 'weight_per_dozen' => '0.4']],
        ])->all();

        $results = $this->race('neck', $payloads);

        $this->assertCount(3, array_filter($results, fn (array $result): bool => $result['code'] === 0));
        $this->assertDatabaseCount('cutting_jobs', 4);
        $this->assertSame('9.000', Decimal::scale((string) CuttingJobRoll::where('cutting_type', 'neck')->sum('dozens')));
        $this->assertSame('4.500', Decimal::scale((string) CuttingJobRoll::where('cutting_type', 'neck')->sum('roll_weight_kg')));
    }

    public function test_simultaneous_mixed_unit_neck_jobs_cannot_overdraw_pieces(): void
    {
        $job = CuttingJob::factory()->create(['cutting_type' => 'body', 'cutting_date' => '2026-10-20']);
        $body = CuttingJobRoll::factory()->create(['cutting_job_id' => $job->id, 'dozens' => '4', 'cut_weight_kg' => '3']);
        $payloads = Staff::factory()->count(4)->create()->map(fn (Staff $staff): array => [
            'staff_id' => $staff->id,
            'cutting_date' => '2026-10-23',
            'rows' => [['source_body_row_id' => $body->id, 'quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '1', 'quantity_pieces' => '4', 'weight_per_dozen' => '0.4']],
        ])->all();
        $results = $this->race('neck', $payloads);
        $this->assertCount(3, array_filter($results, fn (array $result): bool => $result['code'] === 0));
        $totals = CuttingTotals::sum(CuttingJobRoll::where('cutting_type', 'neck')->get());
        $this->assertSame('48.000', $totals['pieces']);
        $this->assertSame('4.000', $totals['dozens']);
        $this->assertSame('3.000', $totals['roll_weight_kg']);
    }
}
