<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_configs', function (Blueprint $table) {
            $table->json('working_hours_one')->nullable()->after('email_one');
            $table->json('working_hours_two')->nullable()->after('email_two');
            $table->json('working_hours_three')->nullable()->after('email_three');
        });
    }

    public function down(): void
    {
        Schema::table('contact_configs', function (Blueprint $table) {
            $table->dropColumn([
                'working_hours_one',
                'working_hours_two',
                'working_hours_three',
            ]);
        });
    }
};
