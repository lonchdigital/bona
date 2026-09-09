<?php

namespace App\Models;

use App\Helpers\PreviewImage;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class BlogArticle extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'preview_text', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function blocks()
    {
        return $this->hasMany(BlogArticleBlock::class)->orderBy('id');
    }

    public function heroImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->hero_image_path) {
                return Storage::url($this->hero_image_path);
            }

            return null;
        });
    }

    /**
     * Absolute URL of the cover image for og:image. Messengers preview jpg far
     * more reliably than webp, and the project stores a jpg next to every webp
     * cover, so the jpg twin is preferred when it is there.
     */
    public function ogImageUrl(): Attribute
    {
        return Attribute::make(fn () => PreviewImage::url($this->hero_image_path));
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['hero_image_url'] = $this->hero_image_url;

        return $array;
    }

    public function scopeAvailableInLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query
            ->whereNotNull("name->{$locale}")
            ->where("name->{$locale}", '<>', '');
    }

    public function hasLocaleVersion(string $locale): bool
    {
        $name = $this->getTranslations('name')[$locale] ?? null;

        return is_string($name) && trim($name) !== '';
    }

    /** @return array<int, string> */
    public function availableLocales(): array
    {
        return collect(app(ApplicationConfigService::class)->getAvailableLanguages())
            ->filter(fn (string $locale) => $this->hasLocaleVersion($locale))
            ->values()
            ->all();
    }

    public function toSitemapTag(): Url|string|array
    {
        return collect($this->availableLocales())
            ->map(function (string $locale) {
                if ($locale === config('app.fallback_locale')) {
                    return route('blog.article.page', ['blogArticleSlug' => $this->slug]);
                }

                return route('localized.blog.article.page', [
                    'lang' => $locale,
                    'blogArticleSlug' => $this->slug,
                ]);
            })
            ->values()
            ->all();
    }
}
