<?php

namespace App\Services\ServicesPage;

use App\Models\Faqs;
use App\Models\ServicesConfig;
use App\Models\ServicesPageSections;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\ServicesPage\DTO\ServicesPageEditDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ServicesPageService extends BaseService
{
    const SERVICES_PAGE_IMAGES_FOLDER = 'services-page-images';

    public function editServicesPage(ServicesPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            $ServicesConfig = $this->getServicesConfig();
            $dataToUpdate = [
                'title' => $request->title,
                'intro' => $request->intro,
                'content' => $request->content,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
            ];

            if ($ServicesConfig) {
                $ServicesConfig->update($dataToUpdate);
            } else {
                $ServicesConfig = ServicesConfig::create($dataToUpdate);
            }

            if ($request->faqsManaged) {
                $this->syncFaqs(ServicesConfig::CONTENT_PAGE_TYPE, $request->faqs);
                $ServicesConfig->touch();
            }

            $this->syncSections($request->sections);

            return ServiceActionResult::make(true, trans('admin.services_edit_success'));
        });
    }

    public function getServicesPageSections(): Collection
    {
        return ServicesPageSections::orderBy('sort_order')->orderBy('id')->get();
    }

    public function getServicesPageSectionsForAdmin(): Collection
    {
        return $this->getServicesPageSections()
            ->map(function (ServicesPageSections $section): array {
                return array_merge($section->toArray(), [
                    'faqs' => $this->getFaqsForAdmin($section->editorialPageType()),
                ]);
            });
    }

    public function getPageFaqs(): Collection
    {
        return $this->getFaqs(ServicesConfig::CONTENT_PAGE_TYPE);
    }

    public function getServiceFaqs(ServicesPageSections $service): Collection
    {
        return $this->getFaqs($service->editorialPageType());
    }

    public function getPageFaqsForAdmin(): array
    {
        return $this->getFaqsForAdmin(ServicesConfig::CONTENT_PAGE_TYPE);
    }

    public function getOtherServices(ServicesPageSections $current, int $limit = 3): Collection
    {
        return ServicesPageSections::query()
            ->where('id', '!=', $current->getKey())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    private function syncSections(?array $sections): void
    {
        $imagesToDelete = [];

        $existingSections = ServicesPageSections::get();
        if ($sections) {
            foreach ($sections as $index => $section) {
                $dataToUpdate = [
                    'title' => $section['title'],
                    'description' => $section['description'],
                    'intro' => $section['intro'] ?? null,
                    'content' => $section['content'] ?? null,
                    'button_text' => $section['button_text'],
                    'button_url' => $section['button_url'],
                    'slug' => $section['slug'],
                    'meta_title' => $section['meta_title'] ?? null,
                    'meta_description' => $section['meta_description'] ?? null,
                    'meta_keywords' => $section['meta_keywords'] ?? null,
                    'meta_tags' => $section['meta_tags'] ?? null,
                    'sort_order' => $index,
                ];

                if (isset($section['image'])) {
                    $sectionImagePath = self::SERVICES_PAGE_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10);

                    $this->storeImage($sectionImagePath, $section['image'], 'webp');
                    $this->storeImage($sectionImagePath, $section['image'], 'jpg');

                    $dataToUpdate['section_image_path'] = $sectionImagePath.'.webp';
                }

                if (isset($section['id']) && $section['id']) {
                    $serviceSection = $existingSections->where('id', $section['id'])->first();
                    if (! $serviceSection) {
                        throw new \Exception('Incorrect slide id: '.$section['id']);
                    }

                    if (isset($section['image'])) {
                        $imagesToDelete[] = $serviceSection->section_image_path;
                    }

                    $serviceSection->update($dataToUpdate);
                } else {
                    $serviceSection = ServicesPageSections::create($dataToUpdate);
                }

                if (($section['faqs_managed'] ?? false) === true || ($section['faqs_managed'] ?? null) === '1') {
                    $this->syncFaqs($serviceSection->editorialPageType(), $section['faqs'] ?? null);
                    $serviceSection->touch();
                }
            }
        }

        $existingSectionsInRequest = $sections ? array_filter(array_column($sections, 'id'), function ($item) {
            return $item !== null;
        }) : [];

        $sectionsToDelete = $existingSections->whereNotIn('id', $existingSectionsInRequest);

        foreach ($sectionsToDelete as $sectionToDelete) {
            Faqs::query()->where('page_type', $sectionToDelete->editorialPageType())->delete();
            if (! str_starts_with((string) $sectionToDelete->section_image_path, 'assets/')) {
                $imagesToDelete[] = $sectionToDelete->section_image_path;
            }
            $sectionToDelete->delete();
        }

        foreach ($imagesToDelete as $imageToDelete) {
            if (! is_null($imageToDelete)) {
                $this->deleteImage($imageToDelete);
            }
        }

    }

    public function getServicesConfig(): ?ServicesConfig
    {
        return ServicesConfig::first();
    }

    private function getFaqs(string $pageType): Collection
    {
        return Faqs::query()
            ->where('page_type', $pageType)
            ->orderBy('id')
            ->get();
    }

    /** @return list<array{id: int, question: array<string, string>, answer: array<string, string>}> */
    private function getFaqsForAdmin(string $pageType): array
    {
        return $this->getFaqs($pageType)
            ->map(fn (Faqs $faq): array => [
                'id' => $faq->id,
                'question' => $faq->getTranslations('question'),
                'answer' => $faq->getTranslations('answer'),
            ])
            ->all();
    }
}
