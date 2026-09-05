<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('orders_count');
            $table->index(['product_type_id', 'sort_order'], 'products_type_sort_order_index');
        });

        $positions = [];

        DB::table('products')
            ->select(['id', 'product_type_id'])
            ->orderBy('product_type_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->each(function (object $product) use (&$positions): void {
                $positions[$product->product_type_id] = ($positions[$product->product_type_id] ?? 0) + 1;

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['sort_order' => $positions[$product->product_type_id]]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_type_sort_order_index');
            $table->dropColumn('sort_order');
        });
    }
};
