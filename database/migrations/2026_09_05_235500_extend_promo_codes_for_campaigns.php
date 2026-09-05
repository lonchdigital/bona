<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('percent')->after('discount');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
            $table->boolean('is_active')->default(true)->after('is_used');
            $table->timestamp('starts_at')->nullable()->after('is_active');
            $table->timestamp('expires_at')->nullable()->after('starts_at');
            $table->unsignedInteger('usage_limit')->nullable()->after('expires_at');
            $table->unsignedInteger('usage_count')->default(0)->after('usage_limit');
            $table->unsignedInteger('max_discounted_items')->nullable()->after('usage_count');
            $table->decimal('minimum_order_amount', 12, 2)->default(0)->after('max_discounted_items');
            $table->decimal('maximum_discount_amount', 12, 2)->nullable()->after('minimum_order_amount');
            $table->boolean('all_products')->default(true)->after('maximum_discount_amount');
        });

        Schema::create('promo_code_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['promo_code_id', 'product_id']);
        });

        // Codes issued by the old email flow were single-use percentage codes.
        // Preserve that contract while allowing newly-created campaigns to be
        // reusable when an administrator leaves their usage limit empty.
        DB::table('promo_codes')->orderBy('id')->eachById(function ($promoCode) {
            DB::table('promo_codes')->where('id', $promoCode->id)->update([
                'discount_type' => 'percent',
                'discount_value' => $promoCode->discount,
                'usage_limit' => 1,
                'usage_count' => $promoCode->is_used ? 1 : 0,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_product');

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'is_active',
                'starts_at',
                'expires_at',
                'usage_limit',
                'usage_count',
                'max_discounted_items',
                'minimum_order_amount',
                'maximum_discount_amount',
                'all_products',
            ]);
        });
    }
};
