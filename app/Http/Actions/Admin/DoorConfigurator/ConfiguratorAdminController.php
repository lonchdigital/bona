<?php

namespace App\Http\Actions\Admin\DoorConfigurator;

use App\Models\DoorConfiguratorItem;
use App\Models\Product;
use App\Services\Product\ConfiguratorCatalog;
use App\Services\Product\ConfiguratorEditor;
use App\Services\Product\ConfiguratorImporter;
use App\Services\Product\ConfiguratorMedia;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConfiguratorAdminController
{
    public function index(Request $request, ConfiguratorCatalog $catalog)
    {
        $kind = $request->input('kind') === 'handle' ? 'handle' : 'door';
        $items = DoorConfiguratorItem::with(['product.colors', 'product.brand'])->where('kind', $kind)->orderBy('sort_order')->orderBy('id')->get();
        $items = $items->filter(function ($item) use ($request) {
            $search = mb_strtolower(trim((string) $request->input('q', '')));
            if ($search !== '' && ! str_contains(mb_strtolower(($item->product?->name ?? '').' '.$item->key.' '.$item->product_id), $search)) {
                return false;
            }
            $category = $request->input('category');
            if ($category && ! in_array($category, array_merge([$item->draft['category'] ?? ''], $item->draft['types'] ?? []), true)) {
                return false;
            }
            $status = $request->input('status');

            return match ($status) {
                'published' => $item->published !== null,
                'draft' => $item->published === null || $item->published !== app(ConfiguratorCatalog::class)->publishable($item->draft),
                'missing' => $this->missingColors($item)->isNotEmpty() || collect($item->draft['colors'] ?? [$item->draft])->contains(fn ($c) => empty($c['image']) || ($item->kind === 'door' && ($item->draft['category'] ?? '') === 'interior' && empty($c['preview']))),
                default => true,
            };
        })->values();
        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator($items->forPage($page, 20), $items->count(), 20, $page, ['path' => $request->url(), 'query' => $request->query()]);
        $catalogProducts = collect();
        if (mb_strlen(trim((string) $request->input('catalog_search'))) >= 2) {
            $term = mb_substr(trim($request->input('catalog_search')), 0, 100);
            $catalogProducts = Product::with('productType')->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')->orWhere('id', ctype_digit($term) ? (int) $term : 0))
                ->whereNotIn('id', DoorConfiguratorItem::where('kind', $kind)->whereNotNull('product_id')->select('product_id'))->limit(30)->get();
        }

        return view('pages.admin.door-configurator.index', compact('kind', 'paginator', 'catalogProducts') + ['managed' => $catalog->managed()]);
    }

    public function edit(DoorConfiguratorItem $item, ConfiguratorCatalog $catalog)
    {
        $item->load(['product.colors', 'product.attributeOptions', 'product.productType.attributes', 'product.brand']);

        return view('pages.admin.door-configurator.edit', [
            'item' => $item, 'issues' => $catalog->issues($item), 'missingColors' => $this->missingColors($item),
            'revisions' => $item->revisions()->with('user')->latest('id')->limit(30)->get(),
        ]);
    }

    public function create(Request $request, ConfiguratorEditor $editor)
    {
        $data = $request->validate(['kind' => 'required|in:door,handle', 'product_id' => 'required|integer|exists:products,id', 'category' => 'required_if:kind,door|in:interior,exterior']);
        $item = DB::transaction(function () use ($data, $editor, $request) {
            // Serialize creates/imports; the unique index is an additional safety net.
            DB::table('door_configurator_state')->where('id', 1)->lockForUpdate()->first();
            if ($existing = DoorConfiguratorItem::where('kind', $data['kind'])->where('product_id', $data['product_id'])->first()) {
                return $existing;
            }
            $product = Product::with('colors')->findOrFail($data['product_id']);
            $draft = $data['kind'] === 'door' ? [
                'category' => $data['category'], 'types' => [], 'short' => $product->getTranslations('name'),
                'crop' => [0, 0, 800, 837], 'handle' => [0.15, 0.55], 'handleSide' => 'left',
                'colors' => $product->colors->isEmpty() ? [['id' => 'default', 'name' => ['uk' => 'Без кольору', 'ru' => 'Без цвета'], 'colorId' => null, 'hex' => '#eeeeee', 'enabled' => false]]
                    : $product->colors->map(fn ($color) => ['id' => 'color-'.$color->id, 'name' => $color->getTranslations('name'), 'colorId' => $color->id, 'hex' => '#eeeeee', 'enabled' => false])->all(),
            ] : ['colorId' => $product->colors->first()?->id, 'color' => '#272929', 'accent' => '#5b5d59', 'shape' => 'square'];
            $item = DoorConfiguratorItem::create(['kind' => $data['kind'], 'key' => $data['kind'].'-'.$product->id, 'product_id' => $product->id, 'draft' => $draft, 'sort_order' => 1000]);
            $editor->record($item, 'create', $request->user()->id);

            return $item;
        });

        return redirect()->route('admin.configurator.edit', $item)->with('success', 'Чернетку відкрито. Додайте матеріали та перевірте примірку перед публікацією.');
    }

    public function save(Request $request, DoorConfiguratorItem $item, ConfiguratorEditor $editor)
    {
        $data = $request->validate(['version' => 'required|integer|min:1', 'payload' => 'required|json|max:200000', 'sort_order' => 'required|integer|between:0,100000']);
        $payload = json_decode($data['payload'], true, 32, JSON_THROW_ON_ERROR);
        abort_unless(is_array($payload), 422);
        $editor->change($item, $data['version'], 'save', $request->user()->id, $payload, $data['sort_order']);

        return back()->with('success', 'Чернетку збережено. На сайті залишається попередня опублікована версія.');
    }

    public function publish(Request $request, DoorConfiguratorItem $item, ConfiguratorEditor $editor)
    {
        $data = $request->validate(['version' => 'required|integer|min:1']);
        $editor->change($item, $data['version'], 'publish', $request->user()->id);

        return back()->with('success', 'Збережену версію опубліковано в конфігураторі.');
    }

    public function hide(Request $request, DoorConfiguratorItem $item, ConfiguratorEditor $editor)
    {
        $data = $request->validate(['version' => 'required|integer|min:1']);
        $editor->change($item, $data['version'], 'hide', $request->user()->id);

        return back()->with('success', 'Приховано лише в конфігураторі. Товар у каталозі та його зображення збережено.');
    }

    public function restore(Request $request, DoorConfiguratorItem $item, ConfiguratorEditor $editor)
    {
        $data = $request->validate(['version' => 'required|integer|min:1', 'revision_id' => 'required|integer']);
        $editor->change($item, $data['version'], 'restore', $request->user()->id, revisionId: $data['revision_id']);

        return back()->with('success', 'Історичну версію відновлено як чернетку. Перевірте її перед публікацією.');
    }

    public function upload(Request $request, DoorConfiguratorItem $item, ConfiguratorMedia $media)
    {
        $request->validate(['image' => 'required|file']);

        return response()->json(['url' => $media->upload($request->file('image'))]);
    }

    public function import(Request $request, ConfiguratorImporter $importer, DoorConfiguratorService $service)
    {
        $data = $request->validate(['mode' => 'required|in:check,apply']);
        $report = $importer->run($service->filePresets(), $data['mode'] === 'check', $request->user()->id);

        return back()->with('import_report', $report)->with('success', $data['mode'] === 'check' ? 'Файли та прив’язки перевірено. Попередній перегляд нічого не змінює; повна перевірка комплектацій виконується під час імпорту.' : 'Готові матеріали імпортовано. Наявні ручні правки збережено.');
    }

    public function preview(Request $request, DoorConfiguratorItem $item, ConfiguratorCatalog $catalog, DoorConfiguratorService $service)
    {
        $data = $catalog->publishable($item->draft);
        $issues = $catalog->issues($item);
        if ($issues) {
            return response()->view('pages.admin.door-configurator.preview-unavailable', compact('issues'), 422)->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
        }
        $presets = $catalog->presets();
        $group = $item->kind === 'door' ? 'products' : 'handles';
        $presets[$group] = [$catalog->preset($item, $data)];
        $request->validate(['locale' => 'sometimes|in:uk,ru']);
        app()->setLocale($request->input('locale', 'uk'));

        return response()->view('pages.store.door-configurator', [
            'configuratorCatalog' => $service->catalog($presets), 'configuratorPreview' => true,
        ])->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
    }

    private function missingColors(DoorConfiguratorItem $item)
    {
        return ($item->product?->colors ?? collect())->whereNotIn('id', collect($item->draft['colors'] ?? [$item->draft])->pluck('colorId'));
    }
}
