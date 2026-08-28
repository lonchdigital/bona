<?php

namespace App\Services\SerpAgent;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Models\Role;
use App\Models\User;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use App\Services\SerpAgent\DTO\SerpAgentArticleDTO;
use App\Services\SerpAgent\Exceptions\SerpAgentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class SerpAgentArticleService extends BaseService
{
    /**
     * Written to blog_articles.external_source so that articles owned by this
     * integration can be updated on a repeated delivery, while articles
     * written by hand in the admin panel are never overwritten.
     */
    const EXTERNAL_SOURCE = 'serp-agent';

    const ARTICLE_IMAGES_FOLDER = 'blog-article-images';

    public function __construct(
        private readonly SerpAgentHtmlService $htmlService,
        private readonly ApplicationConfigService $applicationConfigService,
    ) { }

    /**
     * @return array{action: string, id: int, slug: string, url: string}
     */
    public function storeArticle(SerpAgentArticleDTO $dto): array
    {
        $languages = $this->applicationConfigService->getAvailableLanguages();
        $locale = in_array($dto->locale, $languages, true) ? $dto->locale : (string) config('app.fallback_locale');

        if ($dto->isTranslationsUpdate) {
            return $this->applyTranslationsUpdate($dto);
        }

        $heading = $dto->heading();

        if ($heading === null) {
            throw new SerpAgentException('The payload contains no "h1" and no "title".');
        }

        $body = $this->htmlService->sanitize($dto->content);

        if ($body === '') {
            throw new SerpAgentException('The payload contains no usable "content".');
        }

        // Serp Agent writes the FAQ into the body and also sends it as a list.
        // When the body already carries one it is turned into the accordion in
        // place, and the list is not appended on top of it.
        [$body, $hasInlineFaq] = $this->htmlService->convertInlineFaq($body, $locale);

        /*
         * A body FAQ written as running text rather than headings cannot be
         * turned into the accordion. When the payload carries the same
         * questions as structured data, that flat copy is dropped so the
         * article does not answer everything twice.
         */
        if (!$hasInlineFaq && $dto->faq) {
            $body = $this->htmlService->removeInlineFaq($body, $locale);
        }

        // Question and answer pairs left in the running text get a card of
        // their own rather than opening with bare shorthand.
        $body = $this->htmlService->styleInlineQa($body, $locale);

        $body .= $this->buildAppendix($dto, $locale, $hasInlineFaq);

        $slug = $this->resolveSlug($dto, $heading);
        $existingArticle = $this->findManagedArticle($dto, $slug);

        /*
         * An article here is one record holding every language, so a Russian
         * delivery fills the Russian translations of the article that already
         * exists. Its slug is the URL both languages are served from, so only
         * a delivery in the site's main language may change it.
         */
        if ($existingArticle && $locale !== (string) config('app.fallback_locale')) {
            $slug = $existingArticle->slug;
        }

        $this->guardSlugIsAvailable($slug, $existingArticle);

        $author = $this->resolveAuthor();
        $heroImage = $this->resolveHeroImage($dto, $existingArticle);

        try {
            return $this->coverWithDBTransactionWithoutResponse(
                function () use ($dto, $existingArticle, $slug, $locale, $languages, $heading, $body, $author, $heroImage) {
                    $article = $this->persistArticle(
                        $dto, $existingArticle, $slug, $locale, $languages, $heading, $body, $author, $heroImage['path']
                    );

                    return [
                        'action' => $existingArticle ? 'updated' : 'created',
                        'id' => $article->id,
                        'slug' => $article->slug,
                        'url' => route('blog.article.page', ['blogArticleSlug' => $article->slug]),
                    ];
                }
            );
        } catch (Throwable $throwable) {
            // Images are stored before the transaction opens, so a failed write
            // must not leave an orphan behind. Only a file downloaded for this
            // very delivery may be removed — never the shared default image.
            if ($heroImage['downloaded'] && $heroImage['path']) {
                $this->deleteImage($heroImage['path']);
            }

            throw $throwable;
        }
    }

    /**
     * A translations_updated delivery is a repeat: it refreshes the links
     * between language versions rather than bringing an article.
     *
     * Those links need no refreshing here. Both languages of an article live
     * at the same slug, one under /ru, and the hreflang tags are built from
     * the URL on every request, so they cannot go stale. All that is worth
     * keeping is the group, which is how a later delivery in either language
     * finds this article again.
     */
    private function applyTranslationsUpdate(SerpAgentArticleDTO $dto): array
    {
        $slug = $dto->slug ? Str::slug($dto->slug) : '';
        $article = $this->findManagedArticle($dto, $slug);

        if (!$article) {
            throw new SerpAgentException(
                'No article matches this translation group, so there is nothing to update. Send the article itself first.',
                404
            );
        }

        if ($dto->translationGroupId && $article->translation_group_id !== $dto->translationGroupId) {
            $article->update(['translation_group_id' => $dto->translationGroupId]);
        }

        return [
            'action' => 'translations_acknowledged',
            'id' => $article->id,
            'slug' => $article->slug,
            'url' => route('blog.article.page', ['blogArticleSlug' => $article->slug]),
        ];
    }

    private function persistArticle(
        SerpAgentArticleDTO $dto,
        ?BlogArticle $existingArticle,
        string $slug,
        string $locale,
        array $languages,
        string $heading,
        string $body,
        User $author,
        ?string $heroImagePath,
    ): BlogArticle {
        $previewText = $this->resolvePreviewText($dto, $body, $heading);

        $fields = [
            'slug' => $slug,
            'external_source' => self::EXTERNAL_SOURCE,
            'name' => $this->mergeTranslations($existingArticle, 'name', $heading, $locale, $languages),
            'preview_text' => $this->mergeTranslations($existingArticle, 'preview_text', $previewText, $locale, $languages),
        ];

        if ($dto->externalId) {
            $fields['external_id'] = $dto->externalId;
        }

        if ($dto->translationGroupId) {
            $fields['translation_group_id'] = $dto->translationGroupId;
        }

        foreach ([
            'meta_title' => $dto->metaTitle ?: $heading,
            'meta_description' => $dto->metaDescription,
            'meta_keywords' => $dto->metaKeywords,
        ] as $attribute => $value) {
            $translations = $this->mergeTranslations($existingArticle, $attribute, $value, $locale, $languages);

            if ($translations) {
                $fields[$attribute] = $translations;
            }
        }

        if ($heroImagePath) {
            $fields['hero_image_path'] = $heroImagePath;
        }

        if ($existingArticle) {
            $previousHeroImagePath = $existingArticle->hero_image_path;

            $existingArticle->update($fields);

            if ($heroImagePath
                && $previousHeroImagePath
                && $previousHeroImagePath !== $heroImagePath
                && $previousHeroImagePath !== trim((string) config('serp-agent.default_hero_image'))
            ) {
                $this->deleteImage($previousHeroImagePath);
            }

            $article = $existingArticle;
        } else {
            $fields['creator_id'] = $author->id;

            $article = BlogArticle::create($fields);
        }

        $this->syncTextBlock($article, $body, $locale, $languages);

        return $article;
    }

    /**
     * The article template renders the blocks of an article in insertion order
     * and only knows text, image and video blocks. The whole delivered body
     * therefore lives in a single text block, which is reused on updates so
     * that image or video blocks added by hand survive.
     */
    private function syncTextBlock(BlogArticle $article, string $body, string $locale, array $languages): void
    {
        $textBlock = $article->blocks()
            ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_TEXT)
            ->orderBy('id')
            ->first();

        $content = [];

        if ($textBlock) {
            $existingContent = $textBlock->content;

            if (is_array($existingContent)) {
                $content = $existingContent;
            }
        }

        $content[$locale] = $body;

        if (config('serp-agent.mirror_to_other_locales')) {
            foreach ($languages as $language) {
                if ($language === $locale) {
                    continue;
                }

                if (!isset($content[$language]) || trim((string) $content[$language]) === '') {
                    $content[$language] = $body;
                }
            }
        }

        if ($textBlock) {
            $textBlock->update(['content' => $content]);

            return;
        }

        BlogArticleBlock::create([
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'blog_article_id' => $article->id,
            'content' => $content,
        ]);
    }

    private function buildAppendix(SerpAgentArticleDTO $dto, string $locale, bool $hasInlineFaq = false): string
    {
        $appendix = '';

        if (config('serp-agent.append_faq') && !$hasInlineFaq) {
            $appendix .= $this->htmlService->buildFaqSection($dto->faq, $locale);
        }

        if (config('serp-agent.append_related')) {
            $appendix .= $this->htmlService->buildLinksSection($dto->relatedArticles, 'related', $locale);
            $appendix .= $this->htmlService->buildLinksSection($dto->recommendedResources, 'resources', $locale);
        }

        return $appendix;
    }

    private function resolveSlug(SerpAgentArticleDTO $dto, string $heading): string
    {
        $slug = Str::slug((string) ($dto->slug ?: $heading));

        if ($slug === '') {
            throw new SerpAgentException('Neither "slug" nor the heading can be turned into a URL slug.');
        }

        return Str::limit($slug, 180, '');
    }

    private function findManagedArticle(SerpAgentArticleDTO $dto, string $slug): ?BlogArticle
    {
        // The group is what ties the language versions together, so it is the
        // first thing to look at: without it a Russian delivery would land as
        // a second article.
        if ($dto->translationGroupId) {
            $article = BlogArticle::where('external_source', self::EXTERNAL_SOURCE)
                ->where('translation_group_id', $dto->translationGroupId)
                ->first();

            if ($article) {
                return $article;
            }
        }

        if ($dto->externalId) {
            $article = BlogArticle::where('external_source', self::EXTERNAL_SOURCE)
                ->where('external_id', $dto->externalId)
                ->first();

            if ($article) {
                return $article;
            }
        }

        return BlogArticle::where('slug', $slug)
            ->where('external_source', self::EXTERNAL_SOURCE)
            ->first();
    }

    /**
     * An article written in the admin panel must never be replaced by a
     * delivery that happens to use the same slug.
     */
    private function guardSlugIsAvailable(string $slug, ?BlogArticle $existingArticle): void
    {
        $occupied = BlogArticle::where('slug', $slug)
            ->when($existingArticle, fn ($query) => $query->whereKeyNot($existingArticle->getKey()))
            ->exists();

        if ($occupied) {
            throw new SerpAgentException(
                'The slug "' . $slug . '" already belongs to another article on the site. Change the slug in Serp Agent and send the article again.',
                409
            );
        }
    }

    private function resolvePreviewText(SerpAgentArticleDTO $dto, string $body, string $heading): string
    {
        $previewText = trim((string) ($dto->excerpt ?: $dto->metaDescription));

        if ($previewText === '') {
            $previewText = Str::limit($this->htmlService->toPlainText($body), 300);
        }

        return $previewText !== '' ? $previewText : $heading;
    }

    /**
     * Keeps the translations an editor may have written by hand and fills only
     * what is empty, so a Ukrainian delivery never wipes a Russian version.
     */
    private function mergeTranslations(
        ?BlogArticle $article,
        string $attribute,
        ?string $value,
        string $locale,
        array $languages,
    ): array {
        $translations = [];

        if ($article) {
            $existingTranslations = $article->getTranslations($attribute);

            if (is_array($existingTranslations)) {
                $translations = array_filter($existingTranslations, fn ($item) => is_string($item));
            }
        }

        $value = trim((string) $value);

        if ($value === '') {
            return $translations;
        }

        $translations[$locale] = $value;

        if (config('serp-agent.mirror_to_other_locales')) {
            foreach ($languages as $language) {
                if ($language === $locale) {
                    continue;
                }

                if (!isset($translations[$language]) || trim($translations[$language]) === '') {
                    $translations[$language] = $value;
                }
            }
        }

        return $translations;
    }

    private function resolveAuthor(): User
    {
        $authorEmail = trim((string) config('serp-agent.author_email'));

        if ($authorEmail !== '') {
            $author = User::where('email', $authorEmail)->first();

            if ($author) {
                return $author;
            }

            Log::warning('SerpAgent: SERP_AGENT_AUTHOR_EMAIL does not match any user, falling back to the first admin.', [
                'email' => $authorEmail,
            ]);
        }

        $admin = User::where('role_id', Role::ADMIN_ROLE_ID)->orderBy('id')->first();

        if (!$admin) {
            throw new SerpAgentException('There is no admin user the article could be attributed to.', 500);
        }

        return $admin;
    }

    /**
     * blog_articles.hero_image_path is NOT NULL, so a new article always needs
     * an image. Returns null when the article already has one worth keeping.
     */
    /**
     * @return array{path: ?string, downloaded: bool}
     */
    private function resolveHeroImage(SerpAgentArticleDTO $dto, ?BlogArticle $existingArticle): array
    {
        if ($dto->imageUrl) {
            $downloadedPath = $this->downloadHeroImage($dto->imageUrl);

            if ($downloadedPath) {
                return ['path' => $downloadedPath, 'downloaded' => true];
            }
        }

        if ($existingArticle) {
            return ['path' => null, 'downloaded' => false];
        }

        $defaultHeroImage = trim((string) config('serp-agent.default_hero_image'));

        if ($defaultHeroImage !== '') {
            $copiedPath = $this->copyDefaultHeroImage($defaultHeroImage);

            if ($copiedPath) {
                return ['path' => $copiedPath, 'downloaded' => true];
            }
        }

        throw new SerpAgentException(
            'The payload carries no usable image and SERP_AGENT_DEFAULT_HERO_IMAGE is not configured, while a blog article requires a cover image.'
        );
    }

    /**
     * Every article gets its own copy of the fallback cover instead of sharing
     * one file: deleting an article in the admin panel deletes its cover image,
     * which would otherwise take the fallback away from every future article.
     */
    private function copyDefaultHeroImage(string $defaultHeroImage): ?string
    {
        $disk = Storage::disk(config('app.images_disk_default'));

        if (!$disk->exists($defaultHeroImage)) {
            Log::error('SerpAgent: SERP_AGENT_DEFAULT_HERO_IMAGE points at a file that does not exist.', [
                'path' => $defaultHeroImage,
            ]);

            return null;
        }

        try {
            $extension = pathinfo($defaultHeroImage, PATHINFO_EXTENSION) ?: 'webp';
            $target = self::ARTICLE_IMAGES_FOLDER . '/' . sha1(microtime(true) . $defaultHeroImage) . '_' . Str::random(10);

            $disk->put($target . '.' . $extension, $disk->get($defaultHeroImage));

            // The rest of the project stores a jpg next to every webp cover.
            $jpgCompanion = pathinfo($defaultHeroImage, PATHINFO_DIRNAME)
                . '/' . pathinfo($defaultHeroImage, PATHINFO_FILENAME) . '.jpg';

            if ($extension !== 'jpg' && $disk->exists($jpgCompanion)) {
                $disk->put($target . '.jpg', $disk->get($jpgCompanion));
            }

            return $target . '.' . $extension;
        } catch (Throwable $throwable) {
            Log::error('SerpAgent: the fallback cover image could not be copied.', [
                'path' => $defaultHeroImage,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    private function downloadHeroImage(string $url): ?string
    {
        if (!$this->isDownloadableUrl($url)) {
            Log::warning('SerpAgent: refused to download the cover image.', ['url' => $url]);

            return null;
        }

        try {
            $response = Http::timeout((int) config('serp-agent.image.timeout'))
                ->withOptions(['allow_redirects' => ['max' => 3]])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('SerpAgent: cover image download failed.', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $contents = $response->body();
            $maxBytes = (int) config('serp-agent.image.max_bytes');

            if ($contents === '' || strlen($contents) > $maxBytes) {
                Log::warning('SerpAgent: cover image is empty or too large.', [
                    'url' => $url,
                    'bytes' => strlen($contents),
                ]);

                return null;
            }

            $path = self::ARTICLE_IMAGES_FOLDER . '/' . sha1(microtime(true) . $url) . '_' . Str::random(10);
            $disk = Storage::disk(config('app.images_disk_default'));

            $disk->put($path . '.webp', Image::make($contents)->encode('webp', 70));
            $disk->put($path . '.jpg', Image::make($contents)->encode('jpg', 70));

            return $path . '.webp';
        } catch (Throwable $throwable) {
            Log::warning('SerpAgent: cover image could not be processed.', [
                'url' => $url,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    private function isDownloadableUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        // gethostbyname returns the hostname itself when it cannot resolve it.
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
