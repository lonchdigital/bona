<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
//use Spatie\Translatable\HasTranslations;

class ProductGalleries extends Model
{
//    use HasTranslations;

    protected $guarded = [];


    public function galleryImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->image_path);
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['gallery_image_url'] = $this->gallery_image_url;
        return $array;
    }
}
