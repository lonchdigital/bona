<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Color extends Model
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function parent()
    {
        return $this->belongsTo(Color::class, 'parent_color_id');
    }

    public function children()
    {
        return $this->hasMany(Color::class, 'parent_color_id');
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(function () {
            return Storage::url($this->main_image);
        });
    }

}
