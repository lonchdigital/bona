<?php

use App\Models\User;
use App\Services\Work\WorkContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // A clean CI database has no users. Production already has managers;
        // fresh installations can run works:apply-content after seeding one.
        if (! User::query()->exists()) {
            return;
        }

        app(WorkContentImporter::class)->importFile(
            database_path('content/works/2026_09_27_verified_projects.json'),
        );
    }

    public function down(): void
    {
        // Managers can edit these records after release, so rollback must not
        // delete their copy or uploaded media.
    }
};
