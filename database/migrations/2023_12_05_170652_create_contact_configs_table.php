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
        Schema::create('contact_configs', function (Blueprint $table) {
            $table->id();
            $table->json('city_one')->nullable();
            $table->json('address_one')->nullable();
            $table->json('phone_one')->nullable();
            $table->json('email_one')->nullable();
            $table->text('iframe_address_one')->nullable();

            $table->json('city_two')->nullable();
            $table->json('address_two')->nullable();
            $table->json('phone_two')->nullable();
            $table->json('email_two')->nullable();
            $table->text('iframe_address_two')->nullable();

            $table->json('city_three')->nullable();
            $table->json('address_three')->nullable();
            $table->json('phone_three')->nullable();
            $table->json('email_three')->nullable();
            $table->text('iframe_address_three')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_configs');
    }
};
