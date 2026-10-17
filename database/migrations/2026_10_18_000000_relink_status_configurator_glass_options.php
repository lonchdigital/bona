<?php

use App\Models\DoorConfiguratorItem;
use App\Models\ProductAttributeOptions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // The catalogue's «Скло» attribute.
    private const GLASS_ATTRIBUTE_ID = 10;

    // Admin product saves re-created these glass options under new IDs; the names stayed the same.
    private const STALE_GLASS_OPTIONS = [
        'status-dream-glass' => [7144 => 'Сатин'],
        'status-ultra2-glass-satin' => [7151 => 'Сатин'],
        'status-ultra-black-glass' => [6140 => 'Чорне'],
        'status-fly' => [6097 => 'Сатин'],
    ];

    public function up(): void
    {
        foreach (self::STALE_GLASS_OPTIONS as $key => $staleOptions) {
            $item = DoorConfiguratorItem::where('kind', 'door')->where('key', $key)->first();
            if (! $item?->product_id) {
                continue;
            }

            $replacements = [];
            foreach ($staleOptions as $staleId => $name) {
                if (ProductAttributeOptions::whereKey($staleId)->exists()) {
                    continue;
                }
                $matches = ProductAttributeOptions::where('product_id', $item->product_id)
                    ->where('product_attribute_id', self::GLASS_ATTRIBUTE_ID)
                    ->get()
                    ->filter(fn ($option) => $option->getTranslation('name', 'uk', false) === $name);
                if ($matches->count() === 1) {
                    $replacements[$staleId] = $matches->first()->id;
                }
            }
            if (! $replacements) {
                continue;
            }

            $item->forceFill([
                'draft' => $this->relink($item->draft, $replacements),
                'published' => $this->relink($item->published, $replacements),
            ])->save();
        }
    }

    public function down(): void
    {
        // The stale option IDs no longer exist, so the relinked ones are kept on rollback.
    }

    private function relink(?array $data, array $replacements): ?array
    {
        if ($data === null) {
            return null;
        }
        foreach ($data['colors'] ?? [] as $index => $color) {
            if (isset($color['optionIds'])) {
                $data['colors'][$index]['optionIds'] = array_map(
                    fn ($id) => $replacements[(int) $id] ?? $id,
                    $color['optionIds'],
                );
            }
        }

        return $data;
    }
};
