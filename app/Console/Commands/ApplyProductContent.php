<?php

namespace App\Console\Commands;

use App\Services\Product\ProductContentImporter;
use Illuminate\Console\Command;

class ApplyProductContent extends Command
{
    protected $signature = 'products:apply-content {file : JSON file under database/content/products}';

    protected $description = 'Fill empty or one-line product descriptions, characteristics, FAQ and meta from prepared content';

    public function handle(ProductContentImporter $importer): int
    {
        $path = database_path('content/products/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info("Updated products: {$result['products']}.");
        if ($result['missing'] !== []) {
            $this->warn('Slugs not found: '.implode(', ', $result['missing']));
        }

        return self::SUCCESS;
    }
}
