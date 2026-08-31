<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PruneApplicationLogs extends Command
{
    protected $signature = 'logs:prune {--days= : Override the log retention period, in days}';

    protected $description = 'Rotate the scheduler output and delete application logs older than the retention period';

    public function handle(Filesystem $files): int
    {
        $days = $this->option('days') ?? config('logging.retention_days', 30);

        if (filter_var($days, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $this->error('The retention period must be a positive integer.');

            return self::FAILURE;
        }

        $logDirectory = storage_path('logs');

        if (! $files->isDirectory($logDirectory)) {
            $this->info('The log directory does not exist.');

            return self::SUCCESS;
        }

        $rotated = $this->rotateSchedulerLog($files, $logDirectory);
        $cutoff = now()->subDays((int) $days)->getTimestamp();
        $deleted = 0;

        foreach ($files->files($logDirectory) as $file) {
            if ($file->getFilename() === '.gitignore' || $file->getMTime() >= $cutoff) {
                continue;
            }

            if ($files->delete($file->getPathname())) {
                $deleted++;
            }
        }

        $this->info(sprintf(
            'Log maintenance complete: %d rotated, %d deleted, %d-day retention.',
            $rotated,
            $deleted,
            $days
        ));

        return self::SUCCESS;
    }

    private function rotateSchedulerLog(Filesystem $files, string $logDirectory): int
    {
        $schedulerLog = $logDirectory.'/scheduler.log';

        if (! $files->isFile($schedulerLog) || $files->size($schedulerLog) === 0) {
            return 0;
        }

        $rotatedLog = sprintf(
            '%s/scheduler-%s.log',
            $logDirectory,
            now()->format('Y-m-d-His')
        );

        $files->move($schedulerLog, $rotatedLog);

        return 1;
    }
}
