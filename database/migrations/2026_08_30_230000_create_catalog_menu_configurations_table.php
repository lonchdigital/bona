<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_menu_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_in_header')->default(false);
            $table->unsignedInteger('header_order')->default(0);
            $table->json('cards')->nullable();
            $table->json('columns')->nullable();
            $table->timestamps();
        });

        $productTypes = DB::table('product_types')
            ->where('sort_order', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($productTypes as $index => $productType) {
            $categoryIds = DB::table('categories')
                ->where('product_type_id', $productType->id)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $remainingCategoryIds = $categoryIds->skip(5)->values();
            $columns = $remainingCategoryIds->isEmpty() ? [] : [[
                'title' => [
                    'uk' => 'Інші категорії',
                    'ru' => 'Другие категории',
                ],
                'sort_order' => 0,
                'items' => $remainingCategoryIds->map(fn (int $categoryId, int $itemIndex) => [
                    'category_id' => $categoryId,
                    'label' => ['uk' => '', 'ru' => ''],
                    'url' => ['uk' => '', 'ru' => ''],
                    'sort_order' => $itemIndex,
                ])->all(),
            ]];

            DB::table('catalog_menu_configurations')->insert([
                'product_type_id' => $productType->id,
                'is_visible' => true,
                'sort_order' => $index,
                'show_in_header' => $index < 3,
                'header_order' => $index,
                'cards' => json_encode($categoryIds->take(5)->all(), JSON_UNESCAPED_UNICODE),
                'columns' => json_encode($columns, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_menu_configurations');
    }
};
