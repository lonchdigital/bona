<?php

namespace App\Services\StaticPage;

use App\Models\StaticPage;
use App\Models\StaticPageContent;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\StaticPage\DTO\StaticPageEditDTO;
use Illuminate\Support\Collection;
use App\Services\Application\ApplicationConfigService;

class StaticPageService extends BaseService
{
    public function __construct(
        private readonly ApplicationConfigService $applicationService,
    ){ }

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

        $allData = $staticPage->content;

        if ($staticPage) {
            return [
                'meta_title' => $allData->where('language', $language)->first()?->meta_title,
                'meta_description' => $allData->where('language', $language)->first()?->meta_description,
                'meta_keywords' => $allData->where('language', $language)->first()?->meta_keywords,
                'content' => $allData->where('language', $language)->first()?->content
            ];
        }

        return null;
    }

    public function update(int $staticPageTypeId, StaticPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($staticPageTypeId, $request) {
            $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

            if (!$staticPage) {
                $staticPage = StaticPage::create([
                    'type_id' => $staticPageTypeId,
                ]);
            }

            $data = [];
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
                    'content' => $data['content'][$language],
                ]);
            }

            return ServiceActionResult::make(true, trans('admin.static_page_edit_success'));
        });
    }
}
