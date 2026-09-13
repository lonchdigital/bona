<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurator_cart_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->uuid('request_id');
            $table->char('selection_hash', 64);
            $table->timestamp('created_at');
            $table->unique(['cart_id', 'request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurator_cart_requests');
    }
};
