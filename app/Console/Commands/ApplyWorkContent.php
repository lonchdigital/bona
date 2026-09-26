<?php

namespace App\Console\Commands;

use App\Services\Work\WorkContentImporter;
use Illuminate\Console\Command;

class ApplyWorkContent extends Command
{
    protected $signature = 'works:apply-content {file : JSON file under database/content/works}';

    protected $description = 'Create or fill managed project pages and their verified images';

    public function handle(WorkContentImporter $importer): int
    {
        $path = database_path('content/works/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info("Works updated: {$result['works']}.");
        $this->info("Works created: {$result['created']}.");
        $this->info("Missing managed images restored: {$result['restored_images']}.");

        return self::SUCCESS;
    }
}
