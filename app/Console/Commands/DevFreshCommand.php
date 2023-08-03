<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DevFreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations and seed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('app.env') !== 'local') {
            $this->error('This command can be ran only on local environment!');
            exit();
        }

        $this->output->writeln([
            '',
            '  <fg=white;options=bold;bg=blue> INFO </> Cleaning up storage.',
            '',
        ]);

        $startTime = microtime(true);

        Storage::delete(Storage::disk()->directories('public'));

        $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

        with(new TwoColumnDetail($this->getOutput()))->render(
            'Cleaning up storage',
            "<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>"
        );

        Artisan::call('migrate:fresh', [], $this->getOutput());
        Artisan::call('db:seed', [], $this->getOutput());
    }
}
