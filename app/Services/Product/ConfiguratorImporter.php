<?php

namespace App\Services\Product;

use App\Models\DoorConfiguratorItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfiguratorImporter
{
    // These are the same verified handle-free copies used by the original renderer.
    private const CLEAN = [
        'new-york-ivory.jpg' => 'new-york-ivory-no-handle-v2.webp',
        'new-york-white.jpg' => 'new-york-white-no-handle-v2.webp',
        'new-york-anthracite.jpg' => 'new-york-anthracite-no-handle-v2.webp',
        'ostin.webp' => 'ostin-no-handle-v2.webp', 'molding.webp' => 'molding-no-handle-v2.webp',
        'glasso.webp' => 'glasso-no-handle-v2.webp', 'hidden-primed.webp' => 'hidden-primed-no-handle-v2.webp',
        'hidden-black-edge.webp' => 'hidden-black-edge-no-handle-v2.webp',
        'mirror-silver.webp' => 'mirror-silver-no-handle-v2.webp', 'mirror-bronze.webp' => 'mirror-bronze-no-handle-v2.webp',
        'classic-milan.webp' => 'classic-milan-no-handle-v2.webp', 'classic-nice.webp' => 'classic-nice-no-handle-v2.webp',
    ];

    public function run(array $presets, bool $dryRun = true, ?int $userId = null): array
    {
        return DB::transaction(function () use ($presets, $dryRun, $userId) {
            DB::table('door_configurator_state')->where('id', 1)->lockForUpdate()->first();
            $report = ['created' => 0, 'preserved' => 0, 'published' => 0, 'warnings' => []];
            $media = app(ConfiguratorMedia::class);
            $catalog = app(ConfiguratorCatalog::class);
            foreach (['products' => 'door', 'handles' => 'handle'] as $group => $kind) {
                foreach ($presets[$group] ?? [] as $order => $preset) {
                    $product = Product::where('slug', $preset['slug'])->first();
                    $exists = DoorConfiguratorItem::where('kind', $kind)->where(function ($q) use ($product, $preset) {
                        $q->where('key', $preset['id']);
                        if ($product) {
                            $q->orWhere('product_id', $product->id);
                        }
                    })->exists();
                    if ($exists) {
                        $report['preserved']++;

                        continue;
                    }
                    if (! $product) {
                        $report['warnings'][] = $preset['id'].': товар не знайдено за slug — пропущено.';

                        continue;
                    }
                    $data = $preset;
                    unset($data['id'], $data['slug']);
                    if ($kind === 'door') {
                        foreach ($data['colors'] as &$color) {
                            if (preg_match('/^#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])$/', $color['hex'], $hex)) {
                                $color['hex'] = '#'.$hex[1].$hex[1].$hex[2].$hex[2].$hex[3].$hex[3];
                            }
                            $clean = $color['preview'] ?? self::CLEAN[$color['image']] ?? null;
                            if (($data['category'] ?? '') === 'interior' && ! $clean) {
                                throw new RuntimeException('Немає фото без ручки: '.$preset['id'].' / '.$color['id']);
                            }
                            foreach (array_filter([$color['image'], $clean]) as $file) {
                                $this->checkSource($file);
                            }
                            if (! $dryRun) {
                                $color['image'] = $media->import($color['image']);
                                if ($clean) {
                                    $color['preview'] = $media->import($clean);
                                }
                            }
                        }
                        unset($color);
                    } else {
                        $this->checkSource($data['image']);
                        if (! $dryRun) {
                            $data['image'] = $media->import($data['image']);
                        }
                    }
                    $report['created']++;
                    if ($dryRun) {
                        continue;
                    }
                    $data = $catalog->validateDraft($data, $kind);
                    $item = new DoorConfiguratorItem(['kind' => $kind, 'key' => $preset['id'], 'product_id' => $product->id, 'draft' => $data, 'sort_order' => $order, 'published_sort_order' => $order]);
                    $issues = $catalog->issues($item);
                    // A stale shade never blocks other verified shades of the same door.
                    foreach ($issues as $key => $message) {
                        $report['warnings'][] = $preset['id'].' / '.$key.': '.$message;
                        if (preg_match('/^colors\.(\d+)$/', $key, $match)) {
                            $data['colors'][(int) $match[1]]['enabled'] = false;
                        }
                    }
                    $item->draft = $data;
                    if (! $catalog->issues($item)) {
                        $item->published = $catalog->publishable($data);
                        $item->published_at = now();
                        $report['published']++;
                    }
                    $item->save();
                    app(ConfiguratorEditor::class)->record($item, 'import', $userId);
                }
            }
            if (! $dryRun) {
                if (! DoorConfiguratorItem::where('kind', 'door')->whereNotNull('published')->exists()) {
                    throw new RuntimeException('Немає жодної готової моделі. Імпорт скасовано; старий конфігуратор залишено без змін.');
                }
                DB::table('door_configurator_state')->where('id', 1)->update(['managed' => true]);
            }

            return $report;
        });
    }

    private function checkSource(string $file): void
    {
        $root = realpath(public_path('assets/door-configurator/v1'));
        $path = realpath(public_path('assets/door-configurator/v1/'.$file));
        if (! $root || ! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR) || ! @getimagesize($path)) {
            throw new RuntimeException('Пошкоджений або відсутній файл: '.$file);
        }
    }
}
