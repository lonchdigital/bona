<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_configurator_state', function (Blueprint $table) {
            $table->id();
            $table->boolean('managed')->default(false);
        });
        DB::table('door_configurator_state')->insert(['id' => 1, 'managed' => false]);

        Schema::create('door_configurator_items', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->string('key', 100);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->json('draft');
            $table->json('published')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->integer('sort_order')->default(0);
            $table->integer('published_sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'key']);
            $table->unique(['kind', 'product_id']);
        });

        Schema::create('door_configurator_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('door_configurator_items')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 24);
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_configurator_revisions');
        Schema::dropIfExists('door_configurator_items');
        Schema::dropIfExists('door_configurator_state');
    }
};
