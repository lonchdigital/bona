<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->uuid('bundle_key')->nullable()->after('current_image_path')->index();
            $table->string('bundle_role', 16)->nullable()->after('bundle_key');
            $table->json('bundle_category')->nullable()->after('bundle_role');
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->string('current_image_path')->nullable()->after('attributes_price');
            $table->uuid('bundle_key')->nullable()->after('current_image_path')->index();
            $table->string('bundle_role', 16)->nullable()->after('bundle_key');
            $table->json('bundle_category')->nullable()->after('bundle_role');
        });
    }

    public function down(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropIndex(['bundle_key']);
            $table->dropColumn(['bundle_key', 'bundle_role', 'bundle_category']);
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropIndex(['bundle_key']);
            $table->dropColumn(['current_image_path', 'bundle_key', 'bundle_role', 'bundle_category']);
        });
    }
};
