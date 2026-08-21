<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A project page describes work that was sold, so it can say which service it
 * was and what it cost. That turns the page from a photo caption into
 * something a search engine can present as an offer, and a reader can act on.
 *
 * All optional: a project with none of it filled in shows none of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->json('service_title')->nullable()->after('duration');
            $table->json('service_description')->nullable()->after('service_title');

            $table->decimal('price_from', 10, 2)->nullable()->after('service_description');
            $table->string('price_currency', 3)->nullable()->default('UAH')->after('price_from');
            // "за комплект", "за погонний метр" and so on.
            $table->json('price_note')->nullable()->after('price_currency');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn([
                'service_title', 'service_description',
                'price_from', 'price_currency', 'price_note',
            ]);
        });
    }
};
