<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class ServicesPageSections extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['title', 'description', 'button_text'];

    public function sectionImageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->section_image_path);
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['section_image_url'] = $this->section_image_url;
        return $array;
    }

}
