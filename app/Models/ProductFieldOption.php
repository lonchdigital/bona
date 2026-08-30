<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class ProductFieldOption extends Model
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $guarded = [];

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProductField::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->image_path) {
                return Storage::url($this->image_path);
            }

            return null;
        });
    }

    public function toArray()
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }
}
