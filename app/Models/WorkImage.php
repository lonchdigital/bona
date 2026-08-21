<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class WorkImage extends Model
{
    use HasTranslations;

    public $translatable = ['caption'];

    protected $guarded = [];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(fn () => $this->image_path ? Storage::url($this->image_path) : null);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }
}
