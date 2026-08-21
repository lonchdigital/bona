<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class AboutUsTeamMember extends Model
{
    use HasTranslations;

    public $translatable = ['name', 'role', 'experience', 'quote'];

    protected $guarded = [];

    public function photoUrl(): Attribute
    {
        return Attribute::make(fn () => $this->photo_path ? Storage::url($this->photo_path) : null);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['photo_url'] = $this->photo_url;

        return $array;
    }
}
