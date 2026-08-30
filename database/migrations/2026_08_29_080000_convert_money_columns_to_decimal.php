<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
            $table->decimal('price_in_currency', 12, 2)->nullable()->change();
            $table->decimal('purchase_price_in_currency', 12, 2)->default(0)->change();
        });
        Schema::table('cart_products', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
            $table->decimal('attributes_price', 12, 2)->nullable()->change();
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
            $table->decimal('attributes_price', 12, 2)->nullable()->change();
        });
        Schema::table('product_attribute_options', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->change();
        });
        Schema::table('product_colors', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->change();
        });
        Schema::table('imported_products', function (Blueprint $table) {
            $table->decimal('price_in_currency', 12, 2)->nullable()->change();
            $table->decimal('purchase_price_in_currency', 12, 2)->default(0)->change();
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('rate', 18, 6)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->float('price')->change();
            $table->float('price_in_currency')->nullable()->change();
            $table->float('purchase_price_in_currency')->default(0)->change();
        });
        Schema::table('cart_products', function (Blueprint $table) {
            $table->float('price')->change();
            $table->float('attributes_price')->nullable()->change();
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->float('price')->change();
            $table->float('attributes_price')->nullable()->change();
        });
        Schema::table('product_attribute_options', function (Blueprint $table) {
            $table->float('price')->nullable()->change();
        });
        Schema::table('product_colors', function (Blueprint $table) {
            $table->float('price')->nullable()->change();
        });
        Schema::table('imported_products', function (Blueprint $table) {
            $table->float('price_in_currency')->nullable()->change();
            $table->float('purchase_price_in_currency')->default(0)->change();
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->float('rate')->nullable()->change();
        });
    }
};
