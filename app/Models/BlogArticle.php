<?php

namespace App\Models;

use App\Helpers\PreviewImage;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class BlogArticle extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = ['name', 'slugs', 'preview_text', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (BlogArticle $article): void {
            $slugs = $article->localizedSlugs();
            $names = $article->getTranslations('name');

            if (! empty($names['uk']) && empty($slugs['uk'])) {
                $slugs['uk'] = trim((string) $article->slug)
                    ?: static::slugFromTitle((string) $names['uk'], 'uk');
            }

            if (! empty($names['ru']) && empty($slugs['ru'])) {
                $slugs['ru'] = static::slugFromTitle((string) $names['ru'], 'ru');

                if ($slugs['ru'] === ($slugs['uk'] ?? null)) {
                    $slugs['ru'] = static::appendSlugSuffix($slugs['ru'], 'ru');
                }
            }

            $slugs = array_filter($slugs, fn ($slug) => is_string($slug) && trim($slug) !== '');
            $article->setTranslations('slugs', $slugs);

            if (! empty($slugs['uk'])) {
                // Keep the original scalar column in sync. It preserves every
                // Ukrainian URL and remains a safe rollback path for old code.
                $article->slug = $slugs['uk'];
            } elseif (trim((string) $article->slug) === '' && $slugs !== []) {
                $article->slug = reset($slugs);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function blocks()
    {
        return $this->hasMany(BlogArticleBlock::class)->orderBy('id');
    }

    public function slugRedirects()
    {
        return $this->hasMany(BlogArticleSlugRedirect::class);
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
            ->where("name->{$locale}", '<>', '')
            ->whereNotNull("slugs->{$locale}")
            ->where("slugs->{$locale}", '<>', '');
    }

    public function hasLocaleVersion(string $locale): bool
    {
        $name = $this->getTranslations('name')[$locale] ?? null;
        $slug = $this->localizedSlugs()[$locale] ?? null;

        return is_string($name) && trim($name) !== ''
            && is_string($slug) && trim($slug) !== '';
    }

    /** @return array<string, string> */
    public function localizedSlugs(): array
    {
        $slugs = $this->getTranslations('slugs');

        return is_array($slugs)
            ? array_filter($slugs, fn ($slug) => is_string($slug) && trim($slug) !== '')
            : [];
    }

    public function slugForLocale(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($this->localizedSlugs()[$locale] ?? '');
    }

    public function urlForLocale(string $locale, bool $absolute = false): string
    {
        $slug = $this->slugForLocale($locale);

        if ($locale === (string) config('app.fallback_locale')) {
            return route('blog.article.page', ['blogArticleSlug' => $slug], $absolute);
        }

        return route('localized.blog.article.page', [
            'lang' => $locale,
            'blogArticleSlug' => $slug,
        ], $absolute);
    }

    /** @return array<string, string> */
    public function alternateLinks(): array
    {
        $hreflangs = ['uk' => 'uk-UA', 'ru' => 'ru-UA'];
        $links = [];

        foreach ($this->availableLocales() as $locale) {
            if (isset($hreflangs[$locale])) {
                $links[$hreflangs[$locale]] = $this->urlForLocale($locale, true);
            }
        }

        if ($links !== []) {
            $links['x-default'] = $links['uk-UA'] ?? reset($links);
        }

        return $links;
    }

    public function scopeWhereLocalizedSlug(Builder $query, string $slug, string $locale): Builder
    {
        return $query->where("slugs->{$locale}", $slug);
    }

    /**
     * @return array{article: self, should_redirect: bool}|null
     */
    public static function resolveLocalizedSlug(string $slug, string $locale): ?array
    {
        $article = static::query()->whereLocalizedSlug($slug, $locale)->first();

        if ($article) {
            return ['article' => $article, 'should_redirect' => false];
        }

        $redirect = BlogArticleSlugRedirect::query()
            ->with('article')
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();

        if (! $redirect?->article) {
            return null;
        }

        return ['article' => $redirect->article, 'should_redirect' => true];
    }

    public function rememberPreviousSlug(string $locale, ?string $slug): void
    {
        $slug = trim((string) $slug);

        if ($slug === '' || $slug === $this->slugForLocale($locale)) {
            return;
        }

        BlogArticleSlugRedirect::query()->firstOrCreate([
            'locale' => $locale,
            'slug' => $slug,
        ], [
            'blog_article_id' => $this->id,
        ]);
    }

    public function forgetCurrentSlugRedirects(): void
    {
        foreach ($this->localizedSlugs() as $locale => $slug) {
            BlogArticleSlugRedirect::query()
                ->where('blog_article_id', $this->id)
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->delete();
        }
    }

    public static function slugFromTitle(string $title, string $locale): string
    {
        return trim(Str::limit(Str::slug($title, '-', $locale), 180, ''), '-');
    }

    public static function appendSlugSuffix(string $slug, string $suffix): string
    {
        $suffix = trim($suffix, '-');
        $maxLength = max(1, 180 - strlen($suffix) - 1);

        return trim(Str::limit($slug, $maxLength, ''), '-').'-'.$suffix;
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
                return $this->urlForLocale($locale, true);
            })
            ->values()
            ->all();
    }
}
