<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoorConfiguratorItem extends Model
{
    protected $guarded = [];

    protected $attributes = ['version' => 1, 'sort_order' => 0, 'published_sort_order' => 0];

    protected $casts = ['draft' => 'array', 'published' => 'array', 'published_at' => 'datetime', 'version' => 'integer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DoorConfiguratorRevision::class, 'item_id');
    }
}
