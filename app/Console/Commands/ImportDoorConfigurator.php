<?php

namespace App\Console\Commands;

use App\Services\Product\ConfiguratorImporter;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Console\Command;

class ImportDoorConfigurator extends Command
{
    protected $signature = 'configurator:import
        {--apply : Import into shared storage and activate the admin-managed catalog}
        {--append-shades : Append never-imported interior shades, preserving manual edits and removals}
        {--publish-shades : Publish only newly appended valid shades of already published models}';

    protected $description = 'Check/import prepared configurator materials, preserving all existing admin edits';

    public function handle(ConfiguratorImporter $importer, DoorConfiguratorService $service): int
    {
        if ($this->option('publish-shades') && ! $this->option('append-shades')) {
            $this->error('--publish-shades requires --append-shades.');

            return self::INVALID;
        }
        $report = $importer->run($service->filePresets(), ! $this->option('apply'), appendShades: $this->option('append-shades'), publishShades: $this->option('publish-shades'));
        $this->info($this->option('apply') ? 'Імпорт завершено.' : 'Попередня перевірка: дані та файли не змінено.');
        $this->table(['Нові моделі', 'Збережено наявних', 'Опубліковано моделей'], [[$report['created'], $report['preserved'], $report['published']]]);
        $this->line('Нових відтінків: '.$report['shades_added'].'; опубліковано відтінків: '.$report['shades_published']);
        foreach ($report['warnings'] as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
