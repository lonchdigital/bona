<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PruneApplicationLogsTest extends TestCase
{
    public function test_it_rotates_scheduler_output_and_deletes_logs_older_than_retention(): void
    {
        $originalStoragePath = app()->storagePath();
        $temporaryStoragePath = sys_get_temp_dir().'/bona-log-retention-'.Str::uuid();
        $logDirectory = $temporaryStoragePath.'/logs';

        File::ensureDirectoryExists($logDirectory);
        app()->useStoragePath($temporaryStoragePath);

        try {
            $oldLog = $logDirectory.'/old.log.save';
            $recentLog = $logDirectory.'/recent.log';
            $schedulerLog = $logDirectory.'/scheduler.log';

            File::put($oldLog, 'old');
            File::put($recentLog, 'recent');
            File::put($schedulerLog, 'scheduler output');
            touch($oldLog, now()->subDays(31)->getTimestamp());
            touch($recentLog, now()->subDays(29)->getTimestamp());

            $this->artisan('logs:prune')
                ->expectsOutputToContain('1 rotated, 1 deleted, 30-day retention')
                ->assertSuccessful();

            $this->assertFileDoesNotExist($oldLog);
            $this->assertFileExists($recentLog);
            $this->assertFileDoesNotExist($schedulerLog);
            $this->assertCount(1, File::glob($logDirectory.'/scheduler-*.log'));
        } finally {
            app()->useStoragePath($originalStoragePath);
            File::deleteDirectory($temporaryStoragePath);
        }
    }

    public function test_it_rejects_an_invalid_retention_period(): void
    {
        $this->artisan('logs:prune', ['--days' => '0'])
            ->expectsOutput('The retention period must be a positive integer.')
            ->assertFailed();
    }
}
