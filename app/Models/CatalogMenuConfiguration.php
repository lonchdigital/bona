<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogMenuConfiguration extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'show_in_header' => 'boolean',
            'sort_order' => 'integer',
            'header_order' => 'integer',
            'cards' => 'array',
            'columns' => 'array',
        ];
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }
}
