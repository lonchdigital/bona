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
        Schema::table('orders', function (Blueprint $table) {
            $table->json('sat_city')->after('comment')->nullable();
            $table->json('sat_department')->after('comment')->nullable();

            $table->dropColumn('meest_city');
            $table->dropColumn('meest_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('sat_city');
            $table->dropColumn('sat_department');

            $table->json('meest_city')->after('comment')->nullable();
            $table->json('meest_department')->after('comment')->nullable();
        });
    }
};
