<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL wants the type first and the sign after it: "UNSIGNED BIGINT"
        // is a syntax error and this migration would not run at all.
        // Written as raw SQL because ->change() needs doctrine/dbal, which
        // this project does not have.
        DB::statement('ALTER TABLE wish_lists MODIFY owner_id BIGINT UNSIGNED NULL');

        Schema::table('wish_lists', function (Blueprint $table) {
            $table->string('token')->nullable()->index()->after('owner_id');
        });
    }

    public function down(): void
    {
        DB::statement('DELETE FROM wish_lists WHERE owner_id IS NULL');

        Schema::table('wish_lists', function (Blueprint $table) {
            $table->dropIndex(['token']);
            $table->dropColumn('token');
        });

        DB::statement('ALTER TABLE wish_lists MODIFY owner_id BIGINT UNSIGNED NOT NULL');
    }
};
