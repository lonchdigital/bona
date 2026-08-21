<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users');

            $table->string('slug')->unique();
            $table->json('name');
            $table->json('job_title')->nullable();

            // Short line printed under a blog article, the long one belongs to
            // the author page itself.
            $table->json('short_description')->nullable();
            $table->json('biography')->nullable();

            $table->string('photo_path')->nullable();

            // Fed into the sameAs property of the Person structured data.
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
