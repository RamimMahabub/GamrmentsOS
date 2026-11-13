<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

#[Signature('db:dump {file=database/backup_garmentsos.sql : Output SQL file path}')]
#[Description('Dump current MySQL database to an SQL backup file')]
class ExportDatabaseCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = base_path($this->argument('file'));
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');

        $mysqldump = $this->findMysqldump();

        if (! $mysqldump) {
            $this->error('mysqldump binary not found. Please ensure MySQL/XAMPP/Laragon is installed.');

            return self::FAILURE;
        }

        $command = [
            $mysqldump,
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            "--result-file={$file}",
        ];

        if (! empty($pass)) {
            $command[] = "--password={$pass}";
        }

        $command[] = $db;

        $this->info("Dumping database '{$db}' to {$file}...");
        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Dump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->info('Database dump created successfully!');

        return self::SUCCESS;
    }

    private function findMysqldump(): ?string
    {
        $commonPaths = [
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        ];

        foreach ($commonPaths as $path) {
            if ($path === 'mysqldump') {
                $check = new Process(['where', 'mysqldump']);
                $check->run();
                if ($check->isSuccessful()) {
                    return 'mysqldump';
                }

                continue;
            }

            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
