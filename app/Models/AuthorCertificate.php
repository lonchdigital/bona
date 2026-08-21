<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class AuthorCertificate extends Model
{
    use HasTranslations;

    public $translatable = ['title'];

    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(Author::class);
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

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }
}
