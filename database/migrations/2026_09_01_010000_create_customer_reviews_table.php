<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name', 121);
            // Contact data is kept for verification and is never published.
            $table->string('phone', 19);
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
