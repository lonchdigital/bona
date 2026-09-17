<?php

use App\Models\ApplicationConfig;
use Illuminate\Database\Migrations\Migration;

/**
 * "Disallow: *\/color" hid the colour landing pages the catalog menu links to
 * (grey, anthracite, ivory, concrete doors). They carry their own canonical
 * and title; thin ones are noindexed by the application instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        $config = ApplicationConfig::query()->where('config_name', 'ROBOTS_TXT')->first();
        if (! $config || ! is_string($config->config_data)) {
            return;
        }

        $content = preg_replace('/^[ \t]*Disallow:[ \t]*\*?\/color[ \t]*\r?\n?/mi', '', $config->config_data);

        if (is_string($content) && $content !== $config->config_data) {
            $config->config_data = $content;
            $config->save();
        }
    }

    public function down(): void
    {
        // The previous rule blocked pages that are linked from the menu.
    }
};
