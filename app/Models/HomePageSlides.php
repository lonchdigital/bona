<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class HomePageSlides extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['title', 'description', 'button_text'];

    public function slideImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->slide_image_path ? Storage::url($this->slide_image_path) : null;
        });
    }

    public function slideImageMobileUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->slide_image_path_mobile ? Storage::url($this->slide_image_path_mobile) : null;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['slide_image_url'] = $this->slide_image_url;
        $array['slide_image_mobile_url'] = $this->slide_image_mobile_url;

        return $array;
    }
}
