<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('serp-agent:diagnose', function () {
    $enabled = (bool) config('serp-agent.conversions.enabled');
    $ready = $enabled && (string) config('serp-agent.secret') !== ''
        && (string) config('serp-agent.conversions.project_id') !== '';

    $this->line($ready
        ? 'SERP Agent verification: READY (existing webhook secret; after-response; no network request).'
        : 'SERP Agent verification: INACTIVE (check enabled flag, webhook secret and project ID).');

    return $ready ? 0 : 1;
})->purpose('Check conversion configuration without exposing credentials or sending events');
