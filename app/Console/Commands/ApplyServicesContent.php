<?php

namespace App\Console\Commands;

use App\Services\ServicesPage\ServicesContentImporter;
use Illuminate\Console\Command;

class ApplyServicesContent extends Command
{
    protected $signature = 'services:apply-content {file : JSON file under database/content/services}';

    protected $description = 'Update the services landing page, service details and their FAQs';

    public function handle(ServicesContentImporter $importer): int
    {
        $path = database_path('content/services/'.basename((string) $this->argument('file')));
        if (! is_file($path)) {
            $this->error("Content file not found: {$path}");

            return self::FAILURE;
        }

        $result = $importer->importFile($path);
        $this->info('Services page updated: '.($result['page'] ? 'yes' : 'no').'.');
        $this->info("Service detail pages updated: {$result['services']}.");
        if ($result['missing'] !== []) {
            $this->warn('Service slugs not imported: '.implode(', ', $result['missing']));
        }

        return self::SUCCESS;
    }
}
