<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class HomePageTestimonials extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['name', 'review'];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function testimonialImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->testimonial_image_path);
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['testimonial_image_url'] = $this->testimonial_image_url;
        return $array;
    }
}
