<?php

use App\Support\Product\ProductPageDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->orderBy('id')->chunkById(100, function ($products): void {
            foreach ($products as $product) {
                $blocks = $this->decodeBlocks($product->content_blocks ?? null);
                $existingIds = array_filter(array_column($blocks, 'id'));

                foreach (ProductPageDefaults::blocks() as $defaultBlock) {
                    if (! in_array($defaultBlock['id'], $existingIds, true)) {
                        $blocks[] = $defaultBlock;
                    }
                }

                DB::table('products')->where('id', $product->id)->update([
                    'content_blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('products')->orderBy('id')->chunkById(100, function ($products): void {
            foreach ($products as $product) {
                $blocks = array_values(array_filter(
                    $this->decodeBlocks($product->content_blocks ?? null),
                    fn (array $block): bool => ! in_array($block['id'] ?? null, ProductPageDefaults::ids(), true),
                ));

                DB::table('products')->where('id', $product->id)->update([
                    'content_blocks' => $blocks === []
                        ? null
                        : json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function decodeBlocks(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = is_string($value) ? json_decode($value, true) : [];

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }
};
