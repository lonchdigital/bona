<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CollectionSlide extends Model
{
    protected $guarded = [];

    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    public function image1Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->image_1_path) {
                return Storage::url($this->image_1_path);
            }
            return null;
        });
    }

    public function image2Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->image_2_path) {
                return Storage::url($this->image_2_path);
            }
            return null;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_1_url'] = $this->image_1_url;
        $array['image_2_url'] = $this->image_2_url;

        return $array;
    }
}
