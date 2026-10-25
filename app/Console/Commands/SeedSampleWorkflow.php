<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedSampleWorkflow extends Command
{
    protected $signature = 'garmentos:sample-workflow {--replace : Replace local manufacturing records, keeping the owner and settings}';

    protected $description = 'Seed realistic local yarn, knitting, dyeing and returned-roll examples';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Sample workflow replacement is available only in local and testing environments.');

            return self::FAILURE;
        }
        DB::transaction(function (): void {
            if ($this->option('replace')) {
                foreach (['activity_logs', 'cutting_job_rolls', 'cutting_jobs', 'returned_rolls', 'dyeing_rows', 'dyeing_color_blocks', 'dyeing_jobs', 'colors', 'yarn_stock_movements', 'knitting_dia_row_yarns', 'knitting_dia_rows', 'knitting_job_yarns', 'knitting_jobs', 'lot_sequences', 'lots', 'yarns', 'company_contacts', 'companies'] as $table) {
                    DB::table($table)->delete();
                }
            }
            $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--no-interaction' => true]);
        });
        $this->info('Sample workflow ready: completed dyeing, partial returns, grey fabric awaiting dyeing, and yarn stock. Owner login and settings retained.');

        return self::SUCCESS;
    }
}
