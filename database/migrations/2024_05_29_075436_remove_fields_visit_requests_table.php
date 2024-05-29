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
        Schema::table('visit_requests', function (Blueprint $table) {
            $table->dropColumn('visit_type_id');
            $table->dropColumn('email');
            $table->dropColumn('visit_date');
            $table->dropColumn('visit_time');
            $table->dropColumn('city');
            $table->dropColumn('address');
            $table->dropColumn('entrance_number');
            $table->dropColumn('comment');
            $table->string('form_title')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('visit_type_id');
            $table->string('email')->nullable();
            $table->string('visit_date')->nullable();
            $table->string('visit_time');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('entrance_number')->nullable();
            $table->string('comment')->nullable();
            $table->dropColumn('form_title');
        });
    }
};
