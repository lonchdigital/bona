<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Works were a strip of photographs opening in a lightbox. The audit asks for
 * projects instead: what the client needed, what was fitted, how long it took
 * and what it looks like, each on a page of its own that can link back to the
 * products used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            // What the client came with, and what was done about it.
            $table->json('intro')->after('name')->nullable();
            $table->json('description')->after('intro')->nullable();

            // The facts that make a project concrete enough to be quoted.
            $table->string('location')->after('description')->nullable();
            $table->unsignedSmallInteger('doors_count')->after('location')->nullable();
            $table->string('duration')->after('doors_count')->nullable();

            $table->json('client_quote')->after('duration')->nullable();
            $table->string('client_name')->after('client_quote')->nullable();

            $table->boolean('is_published')->after('client_name')->default(true);
            $table->unsignedInteger('sort_order')->after('is_published')->default(0);
        });

        Schema::create('work_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('work_id');
            $table->foreign('work_id')->references('id')->on('works')->onDelete('cascade');

            $table->string('image_path');
            $table->json('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_images');

        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn([
                'intro', 'description', 'location', 'doors_count', 'duration',
                'client_quote', 'client_name', 'is_published', 'sort_order',
            ]);
        });
    }
};
