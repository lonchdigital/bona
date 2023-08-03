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
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_type_id');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('visit_date')->nullable();
            $table->string('visit_time');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('entrance_number')->nullable();
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_requests');
    }
};
