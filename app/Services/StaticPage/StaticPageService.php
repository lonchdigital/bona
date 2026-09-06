<?php

namespace App\Services\StaticPage;

use App\Models\StaticPage;
use App\Models\StaticPageContent;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\StaticPage\DTO\StaticPageEditDTO;
use Illuminate\Support\Collection;

class StaticPageService extends BaseService
{
    public function __construct(
        private readonly ApplicationConfigService $applicationService,
    ) {}

    public function getContent(int $staticPageTypeId): Collection
    {
        $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

        if ($staticPage) {
            return $staticPage->content;
        }

        return collect();
    }

    public function getContentByLanguage(int $staticPageTypeId, string $language): ?string
    {
        $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

        if ($staticPage) {
            return $staticPage->content->where('language', $language)->first()?->content;
        }

        return null;
    }

    public function getAllDataByLanguage(int $staticPageTypeId, string $language): ?array
    {
        $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

        if (! $staticPage) {
            return null;
        }

        $content = $staticPage->content()
            ->where('language', $language)
            ->first();

        return [
            'meta_title' => $content?->meta_title,
            'meta_description' => $content?->meta_description,
            'meta_keywords' => $content?->meta_keywords,
            'meta_tags' => $content?->meta_tags,
            'content' => $content?->content,
        ];
    }

    public function update(int $staticPageTypeId, StaticPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($staticPageTypeId, $request) {
            $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

            if (! $staticPage) {
                $staticPage = StaticPage::create([
                    'type_id' => $staticPageTypeId,
                ]);
            }

            $data = [];
            $request->meta_tags = [
                'uk' => $request->meta_tags,
                'ru' => $request->meta_tags,
            ];
            foreach ($request as $key => $value) {
                $data[$key] = $value;
            }

            foreach ($this->applicationService->getAvailableLanguages() as $language) {
                StaticPageContent::updateOrCreate([
                    'static_page_id' => $staticPage->id,
                    'language' => $language,
                ], [
                    'meta_title' => $data['meta_title'][$language],
                    'meta_description' => $data['meta_description'][$language],
                    'meta_keywords' => $data['meta_keywords'][$language],
                    'meta_tags' => $data['meta_tags'][$language],
                    'content' => $data['content'][$language],
                ]);
            }

            return ServiceActionResult::make(true, trans('admin.static_page_edit_success'));
        });
    }
}
