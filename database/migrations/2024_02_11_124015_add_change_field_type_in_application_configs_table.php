<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('application_configs', function (Blueprint $table) {
            $table->dropColumn('data');
            $table->json('config_data')->nullable()->after('config_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_configs', function (Blueprint $table) {
            $table->dropColumn('config_data');
            $table->text('data')->after('config_name');
        });
    }
};
