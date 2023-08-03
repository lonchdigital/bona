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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('status_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->foreign('promo_code_id')
                ->references('id')
                ->on('promo_codes');
            $table->integer('delivery_type_id')->nullable();
            $table->integer('payment_type_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->foreign('region_id')
                ->references('id')
                ->on('regions');
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('building_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('floor_number')->nullable();
            $table->boolean('has_elevator')->default(false);
            $table->string('delivery_date')->nullable();
            $table->integer('delivery_time_id')->nullable();

            $table->integer('recipient_type_id');
            $table->string('custom_recipient_first_name')->nullable();
            $table->string('custom_recipient_last_name')->nullable();
            $table->string('custom_recipient_phone')->nullable();
            $table->string('custom_recipient_email')->nullable();

            $table->string('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
