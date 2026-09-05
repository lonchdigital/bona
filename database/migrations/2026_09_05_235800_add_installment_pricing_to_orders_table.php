<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('installment_provider', 20)->nullable()->after('payment_type_id');
            $table->unsignedTinyInteger('installment_period')->nullable()->after('installment_provider');
            $table->decimal('installment_surcharge_percent', 5, 2)->nullable()->after('installment_period');
            $table->decimal('installment_surcharge_amount', 12, 2)->default(0)->after('installment_surcharge_percent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'installment_provider',
                'installment_period',
                'installment_surcharge_percent',
                'installment_surcharge_amount',
            ]);
        });
    }
};
