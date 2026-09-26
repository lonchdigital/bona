<?php

namespace App\Console\Commands;

use App\Services\Product\ConfiguratorImporter;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Console\Command;

class ImportDoorConfigurator extends Command
{
    protected $signature = 'configurator:import {--apply : Import into shared storage and activate the admin-managed catalog}';

    protected $description = 'Check/import prepared configurator materials, preserving all existing admin edits';

    public function handle(ConfiguratorImporter $importer, DoorConfiguratorService $service): int
    {
        $report = $importer->run($service->filePresets(), ! $this->option('apply'));
        $this->info($this->option('apply') ? 'Імпорт завершено.' : 'Попередня перевірка: дані та файли не змінено.');
        $this->table(['Нові', 'Збережено без змін', 'Опубліковано'], [[$report['created'], $report['preserved'], $report['published']]]);
        foreach ($report['warnings'] as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
