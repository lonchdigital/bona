<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
            return Storage::url($this->slide_image_path);
        });
    }
    public function slideImageMobileUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->slide_image_path_mobile);
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
