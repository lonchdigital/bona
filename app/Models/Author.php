<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class Author extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = [
        'name',
        'job_title',
        'short_description',
        'biography',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function certificates()
    {
        return $this->hasMany(AuthorCertificate::class)->orderBy('sort_order')->orderBy('id');
    }

    public function photoUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->photo_path) {
                return Storage::url($this->photo_path);
            }

            return null;
        });
    }

    /**
     * Absolute URL of the photo for og:image and structured data. The jpg twin
     * is preferred: messengers preview it far more reliably than webp.
     */
    public function ogImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if (!$this->photo_path) {
                return null;
            }

            $path = $this->photo_path;

            $jpgPath = pathinfo($path, PATHINFO_DIRNAME)
                . '/' . pathinfo($path, PATHINFO_FILENAME) . '.jpg';

            if (Storage::disk(config('app.images_disk_default'))->exists($jpgPath)) {
                $path = $jpgPath;
            }

            return url(Storage::url($path));
        });
    }

    /**
     * The profiles the Person structured data points at through sameAs.
     *
     * @return array<int, string>
     */
    public function sameAsLinks(): array
    {
        return array_values(array_filter([
            $this->instagram_url,
            $this->facebook_url,
            $this->linkedin_url,
        ]));
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['photo_url'] = $this->photo_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        return [
            route('store.author.page', ['authorSlug' => $this->slug]),
            '/ru' . route('store.author.page', ['authorSlug' => $this->slug], false),
        ];
    }
}
