<?php

namespace App\Console\Commands;

use App\Services\ProductCategory\CategoryContentImporter;
use Illuminate\Console\Command;

class ApplyCategoryContent extends Command
{
    protected $signature = 'categories:apply-content {file : JSON file under database/content/categories}';

    protected $description = 'Fill empty category SEO text and meta from prepared content';

    public function handle(CategoryContentImporter $importer): int
    {
        $path = database_path('content/categories/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info("Updated categories: {$result['categories']}.");
        if ($result['missing'] !== []) {
            $this->warn('Slugs not found: '.implode(', ', $result['missing']));
        }

        return self::SUCCESS;
    }
}
