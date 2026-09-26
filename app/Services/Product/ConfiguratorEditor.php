<?php

namespace App\Services\Product;

use App\Models\DoorConfiguratorItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfiguratorEditor
{
    public function __construct(private ConfiguratorCatalog $catalog) {}

    public function change(DoorConfiguratorItem $item, int $version, string $action, ?int $userId, ?array $draft = null, ?int $order = null, ?int $revisionId = null): DoorConfiguratorItem
    {
        return DB::transaction(function () use ($item, $version, $action, $userId, $draft, $order, $revisionId) {
            $item = DoorConfiguratorItem::lockForUpdate()->findOrFail($item->id);
            if ($item->version !== $version) {
                throw ValidationException::withMessages(['version' => 'Модель уже змінена в іншій вкладці. Оновіть сторінку перед повторним збереженням.']);
            }
            if ($action === 'save') {
                $item->draft = $this->catalog->validateDraft($draft, $item->kind);
            } elseif ($action === 'restore') {
                $revision = $item->revisions()->findOrFail($revisionId);
                $item->draft = $revision->snapshot['draft'];
                $item->sort_order = $revision->snapshot['sort_order'];
            } elseif ($action === 'publish') {
                if ($issues = $this->catalog->issues($item)) {
                    throw ValidationException::withMessages($issues);
                }
                $item->published = $this->catalog->publishable($item->draft);
                $item->published_at = now();
                $item->published_sort_order = $item->sort_order;
            } elseif ($action === 'hide') {
                $item->published = null;
                $item->published_at = null;
            } else {
                throw new \InvalidArgumentException('Unknown configurator action');
            }
            if ($order !== null) {
                $item->sort_order = $order;
            }
            $item->version++;
            $item->save();
            $this->record($item, $action, $userId);

            return $item;
        });
    }

    public function record(DoorConfiguratorItem $item, string $action, ?int $userId = null): void
    {
        $item->revisions()->create([
            'user_id' => $userId, 'action' => $action, 'version' => $item->version,
            'snapshot' => ['draft' => $item->draft, 'published' => $item->published, 'sort_order' => $item->sort_order],
        ]);
    }
}
