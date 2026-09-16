<?php

use App\Models\Product;
use App\Models\ProductSlugRedirect;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            // A renamed product: its current slug is resolved on every hit, so
            // later renames never build redirect chains.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            // A removed product: the closest listing instead of a 404.
            $table->string('target_path')->nullable();
            $table->timestamps();
        });

        // Old product addresses Google Search Console reported as 404,
        // matched to the product they became or the nearest catalog page.
        $map = json_decode((string) file_get_contents(database_path('content/redirects/2026_09_17_search_console_404.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($map as $oldSlug => $target) {
            if (Product::query()->where('slug', $oldSlug)->exists()) {
                continue;
            }

            $productId = isset($target['product'])
                ? Product::query()->where('slug', $target['product'])->value('id')
                : null;
            $path = $target['path'] ?? null;

            if ($productId === null && $path === null) {
                continue;
            }

            ProductSlugRedirect::query()->updateOrCreate(
                ['slug' => $oldSlug],
                ['product_id' => $productId, 'target_path' => $productId ? null : $path],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_slug_redirects');
    }
};
