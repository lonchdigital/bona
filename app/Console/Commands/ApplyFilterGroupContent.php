<?php

namespace App\Console\Commands;

use App\Services\FilterGroups\FilterGroupContentImporter;
use Illuminate\Console\Command;

class ApplyFilterGroupContent extends Command
{
    protected $signature = 'filter-groups:apply-content {file : JSON file under database/content/filter-groups}';

    protected $description = 'Create or update filter-group landing pages, their SEO text, FAQ and meta';

    public function handle(FilterGroupContentImporter $importer): int
    {
        $path = database_path('content/filter-groups/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info("Updated filter groups: {$result['groups']}.");
        if ($result['missing'] !== []) {
            $this->warn('Slugs not imported: '.implode(', ', $result['missing']));
        }

        return self::SUCCESS;
    }
}
