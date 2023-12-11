<?php

namespace App\Services\StaticPage;

use App\Models\StaticPage;
use App\Models\StaticPageContent;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\StaticPage\DTO\SeoTextsEditDTO;
use App\Services\StaticPage\DTO\StaticPageEditDTO;
use Illuminate\Support\Collection;

class StaticPageService extends BaseService
{
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

    public function update(int $staticPageTypeId, StaticPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($staticPageTypeId, $request) {
            $staticPage = StaticPage::where('type_id', $staticPageTypeId)->first();

            if (!$staticPage) {
                $staticPage = StaticPage::create([
                    'type_id' => $staticPageTypeId,
                ]);
            }

            foreach ($request->content as $language => $content) {
                StaticPageContent::updateOrCreate([
                    'static_page_id' => $staticPage->id,
                    'language' => $language,
                ], [
                    'content' => $content
                ]);
            }

            return ServiceActionResult::make(true, trans('admin.static_page_edit_success'));
        });
    }
}
