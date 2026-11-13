<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('db:restore {file=database/backup_garmentsos.sql : Path to the SQL dump file}')]
#[Description('Restore database tables and data from an SQL dump file')]
class ImportDatabaseCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));

        if (! File::exists($filePath)) {
            $this->error("Backup file not found at: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Reading SQL dump from {$filePath}...");
        $sql = File::get($filePath);

        $this->warn('Wiping existing tables to ensure a clean restore...');
        $this->call('db:wipe', ['--force' => true]);

        $this->info('Importing database tables and records...');
        DB::unprepared($sql);

        $this->info('Database restored successfully from backup!');

        return self::SUCCESS;
    }
}
