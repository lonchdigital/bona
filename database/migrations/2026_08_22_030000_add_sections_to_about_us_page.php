<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The About us page was a single paragraph beside a picture. The audit asks it
 * to carry the things a buyer looks for before parting with money: how long
 * the company has been around, how the work actually goes, and who is behind
 * it.
 *
 * Every field is optional. A section with nothing in it renders nothing at
 * all, so the page can be filled in a piece at a time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_us_configs', function (Blueprint $table) {
            $table->json('facts_title')->nullable()->after('iframe');

            $table->json('history_title')->nullable()->after('facts_title');
            $table->json('history_text')->nullable()->after('history_title');

            $table->json('steps_title')->nullable()->after('history_text');
            $table->json('team_title')->nullable()->after('steps_title');

            $table->json('cta_title')->nullable()->after('team_title');
            $table->json('cta_text')->nullable()->after('cta_title');
            $table->json('cta_button_text')->nullable()->after('cta_text');
            $table->string('cta_button_url')->nullable()->after('cta_button_text');
        });

        // Figures worth stating plainly: since 2013, two showrooms, and so on.
        Schema::create('about_us_facts', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->json('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Consultation, measuring, choosing, delivery, fitting.
        Schema::create('about_us_steps', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('about_us_team_members', function (Blueprint $table) {
            $table->id();
            $table->string('photo_path')->nullable();
            $table->json('name');
            $table->json('role')->nullable();
            $table->json('experience')->nullable();
            $table->json('quote')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_us_team_members');
        Schema::dropIfExists('about_us_steps');
        Schema::dropIfExists('about_us_facts');

        Schema::table('about_us_configs', function (Blueprint $table) {
            $table->dropColumn([
                'facts_title', 'history_title', 'history_text', 'steps_title',
                'team_title', 'cta_title', 'cta_text', 'cta_button_text',
                'cta_button_url',
            ]);
        });
    }
};
