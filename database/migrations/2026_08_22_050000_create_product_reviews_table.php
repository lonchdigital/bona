<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews customers leave on this site, about a particular product.
 *
 * They have to be collected here rather than copied over from anywhere else:
 * only a review written on the site can legitimately carry Review markup, and
 * only a review about the product itself belongs inside Product.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->string('author_name');
            // Never published; kept so a review can be verified or followed up.
            $table->string('author_email')->nullable();

            $table->unsignedTinyInteger('rating');
            $table->text('review');

            $table->unsignedTinyInteger('status_id')->default(1);
            $table->timestamp('published_at')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
