<?php

namespace App\Services\SerpAgent;

use App\Jobs\RegenerateSitemapJob;
use App\Models\BlogArticle;
use App\Models\BlogArticleRetirement;
use App\Models\BlogArticleSlugRedirect;
use App\Services\Base\BaseService;
use App\Services\SerpAgent\DTO\SerpAgentRetirementDTO;
use App\Services\SerpAgent\Exceptions\SerpAgentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Takes one language version of a Serp Agent article off the site and answers
 * its old address with a permanent redirect, or with 410 when Serp Agent names
 * no target. The article row stays, so a later publication brings it back.
 */
class SerpAgentArticleRetirementService extends BaseService
{
    /**
     * @return array{status: string, url: string, redirectTo: ?string}
     */
    public function retire(SerpAgentRetirementDTO $dto): array
    {
        $article = $this->findArticle($dto);

        if (! $article) {
            throw new SerpAgentException('На сайті немає статті з таким slug і мовою, опублікованої через Serp Agent.', 404);
        }

        $url = $article->urlForLocale($dto->locale, true);

        if ($already = $this->previousRetirement($dto, $article)) {
            return ['status' => 'already', 'url' => $url, 'redirectTo' => $already->redirect_to];
        }

        if ($dto->redirectTo !== null) {
            $this->guardRedirectTarget($dto->redirectTo, $article, $dto->locale);
        }

        if ($dto->isTest) {
            return ['status' => 'test', 'url' => $url, 'redirectTo' => $dto->redirectTo];
        }

        $retirement = DB::transaction(function () use ($dto, $article) {
            // Serialises two deliveries for the same article, so a version is never retired twice.
            BlogArticle::whereKey($article->id)->lockForUpdate()->first();

            $active = $article->activeRetirements()->where('locale', $dto->locale)->first();

            if ($active) {
                return null;
            }

            return $article->retirements()->create([
                'locale' => $dto->locale,
                'slug' => $article->slugForLocale($dto->locale),
                'redirect_to' => $dto->redirectTo,
                'delivery_id' => $dto->deliveryId,
                'retired_at' => now(),
            ]);
        });

        if (! $retirement) {
            return ['status' => 'already', 'url' => $url, 'redirectTo' => $article->retirementFor($dto->locale)?->redirect_to];
        }

        $article->unsetRelation('activeRetirements');
        $this->refreshSitemap();

        return [
            'status' => $retirement->redirect_to ? 'redirected' : 'gone',
            'url' => $url,
            'redirectTo' => $retirement->redirect_to,
        ];
    }

    /** A new publication brings a retired version back: its old address opens again. */
    public function restore(BlogArticle $article, string $locale): void
    {
        if (! $article->activeRetirements()->where('locale', $locale)->update(['restored_at' => now()])) {
            return;
        }

        $article->unsetRelation('activeRetirements');
        DB::afterCommit(fn () => $this->refreshSitemap());
    }

    /**
     * Synchronous on purpose: Serp Agent checks sitemap.xml right after the
     * answer, and the queued unique job is skipped while an old lock is held.
     */
    private function refreshSitemap(): void
    {
        try {
            RegenerateSitemapJob::dispatchSync();
        } catch (Throwable $throwable) {
            Log::error('SerpAgent: sitemap.xml was not regenerated after a retirement change. '.$throwable->getMessage());
        }
    }

    /**
     * Only articles that came through this integration are looked at, so an
     * editorial post with the same slug is never touched. The address Serp
     * Agent holds is the fallback for a slug the site had to change.
     */
    private function findArticle(SerpAgentRetirementDTO $dto): ?BlogArticle
    {
        $candidates = array_unique(array_filter([$dto->slug, $this->slugFromUrl($dto->url, $dto->locale)]));

        foreach ($candidates as $slug) {
            $article = BlogArticle::query()
                ->where('external_source', SerpAgentArticleService::EXTERNAL_SOURCE)
                ->whereLocalizedSlug($slug, $dto->locale)
                ->first()
                ?? BlogArticleSlugRedirect::query()
                    ->where('locale', $dto->locale)
                    ->where('slug', $slug)
                    ->first()
                    ?->article;

            if ($article
                && $article->external_source === SerpAgentArticleService::EXTERNAL_SOURCE
                && $article->hasLocaleContent($dto->locale)) {
                return $article;
            }
        }

        return null;
    }

    /** A repeated delivery, or a version that is already off the site. */
    private function previousRetirement(SerpAgentRetirementDTO $dto, BlogArticle $article): ?BlogArticleRetirement
    {
        return BlogArticleRetirement::query()->where('delivery_id', $dto->deliveryId)->first()
            ?? $article->retirementFor($dto->locale);
    }

    /**
     * The target must be a live page of this site that answers on its own:
     * anything else would leave visitors on a chain or on another domain.
     */
    private function guardRedirectTarget(string $redirectTo, BlogArticle $article, string $locale): void
    {
        $siteHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if (strtolower((string) parse_url($redirectTo, PHP_URL_HOST)) !== $siteHost) {
            throw new SerpAgentException('redirectTo веде на інший домен. Потрібна адреса на '.$siteHost.'.');
        }

        $target = $this->articleBehind($redirectTo);

        if ($target && $target['article']->is($article) && $target['locale'] === $locale) {
            throw new SerpAgentException('redirectTo веде на саму статтю, яку треба прибрати.');
        }

        if ($target && $target['article']->retirementFor($target['locale'])) {
            throw new SerpAgentException('redirectTo веде на іншу прибрану статтю. Вкажіть сторінку, яка відкривається.');
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['User-Agent' => 'BonaSerpAgentRetireCheck/1.0'])
                ->get($redirectTo);
        } catch (Throwable) {
            throw new SerpAgentException('Не вдалося відкрити redirectTo, щоб перевірити, що сторінка жива.');
        }

        if ($response->status() !== 200) {
            throw new SerpAgentException(
                'redirectTo не відкривається як жива сторінка: відповідає кодом '.$response->status().'.'
            );
        }
    }

    /**
     * @return array{article: BlogArticle, locale: string}|null
     */
    private function articleBehind(string $url): ?array
    {
        $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');

        if (! preg_match('#^(?:/(ru))?/blog/(?:article/)?([^/]+)$#', $path, $matches)) {
            return null;
        }

        $locale = $matches[1] ?: (string) config('app.fallback_locale');
        $resolved = BlogArticle::resolveLocalizedSlug(rawurldecode($matches[2]), $locale);

        return $resolved ? ['article' => $resolved['article'], 'locale' => $locale] : null;
    }

    private function slugFromUrl(?string $url, string $locale): ?string
    {
        $prefix = $locale === (string) config('app.fallback_locale') ? '' : '/'.$locale;
        $path = (string) parse_url((string) $url, PHP_URL_PATH);

        return preg_match('#^'.preg_quote($prefix, '#').'/blog/([^/]+)/?$#', $path, $matches)
            ? rawurldecode($matches[1])
            : null;
    }
}
