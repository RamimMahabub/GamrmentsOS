<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmptyOperationalDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:empty-operational {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empty all production/operational data while preserving user credentials and core settings.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tablesToTruncate = [
            'lot_packing_materials',
            'packing_job_rows',
            'packing_jobs',
            'packing_material_stock_movements',
            'packing_materials',
            'sewing_job_rows',
            'sewing_jobs',
            'cutting_job_rolls',
            'cutting_jobs',
            'returned_rolls',
            'dyeing_rows',
            'dyeing_color_blocks',
            'dyeing_jobs',
            'knitting_dia_row_yarns',
            'knitting_dia_rows',
            'knitting_job_yarns',
            'knitting_jobs',
            'yarn_stock_movements',
            'yarns',
            'factory_expenses',
            'thread_types',
            'company_contacts',
            'companies',
            'colors',
            'staffs',
            'activity_logs',
            'lots',
            'lot_sequences',
        ];

        $this->warn('Preserving users, passwords, sessions, settings, and migrations.');
        $this->info('Truncating operational tables: '.implode(', ', $tablesToTruncate));

        Schema::disableForeignKeyConstraints();

        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("Truncated table: {$table}");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Operational database has been successfully emptied.');
        $this->info('User count remaining: '.DB::table('users')->count());

        return Command::SUCCESS;
    }
}
