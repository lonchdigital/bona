<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class BlogSlide extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['description'];

    public function slideImage1Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->slide_image_1_path) {
                return Storage::url($this->slide_image_1_path);
            }
            return null;
        });
    }

    public function slideImage2Url(): Attribute
    {
        return Attribute::make(function () {
            if ($this->slide_image_2_path) {
                return Storage::url($this->slide_image_2_path);
            }
            return null;
        });
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['slide_image_1_url'] = $this->slide_image_1_url;
        $array['slide_image_2_url'] = $this->slide_image_2_url;

        return $array;
    }
}
