<?php

namespace App\Services\FilterGroups;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Country;
use App\Models\Faqs;
use App\Models\FilterGroup;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductTypeSizeOption;
use App\Models\SeoText;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\FilterGroups\DTO\FilterGroupEditDTO;
use Illuminate\Support\Collection;

class FilterGroupService extends BaseService
{
    public function getFilterGroupsPaginated()
    {
        return FilterGroup::with(['creator', 'productType'])->paginate(config('domain.items_per_page'));
    }

    public function createFilterGroup(FilterGroupEditDTO $request, User $creator): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request, $creator) {

            $filterGroup = FilterGroup::create([
                'user_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'title_tag' => $request->titleTag,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeywords,
                'product_type_id' => $request->productTypeId,
                'filters' => [
                    'price_from' => $request->priceFrom,
                    'price_to' => $request->priceTo,

                    'country_ids' => $request->countryIds,

                    'custom_fields' => $request->customFields,

                    'color_ids' => $request->colorIds,

                    'brand_ids' => $request->brandIds,

                    'length_from' => $request->lengthFrom,
                    'length_to' => $request->lengthTo,
                    'length_options' => $request->lengthOptions,

                    'width_from' => $request->widthFrom,
                    'width_to' => $request->widthTo,
                    'width_options' => $request->widthOptions,

                    'height_from' => $request->heightFrom,
                    'height_to' => $request->heightTo,
                    'height_options' => $request->heightOptions,
                ],
            ]);

            $this->syncEditorialContent($filterGroup, $request);

            return ServiceActionResult::make(true, trans('admin.filter_groups_create_success'));
        });
    }

    public function editFilterGroup(FilterGroup $filterGroup, FilterGroupEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($filterGroup, $request) {
            $filterGroup->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'title_tag' => $request->titleTag,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeywords,
                'product_type_id' => $request->productTypeId,
                'filters' => [
                    'price_from' => $request->priceFrom,
                    'price_to' => $request->priceTo,

                    'country_ids' => $request->countryIds,

                    'custom_fields' => $request->customFields,

                    'color_ids' => $request->colorIds,

                    'brand_ids' => $request->brandIds,

                    'length_from' => $request->lengthFrom,
                    'length_to' => $request->lengthTo,
                    'length_options' => $request->lengthOptions,

                    'width_from' => $request->widthFrom,
                    'width_to' => $request->widthTo,
                    'width_options' => $request->widthOptions,

                    'height_from' => $request->heightFrom,
                    'height_to' => $request->heightTo,
                    'height_options' => $request->heightOptions,
                ],
            ]);

            $this->syncEditorialContent($filterGroup, $request);

            return ServiceActionResult::make(true, trans('admin.filter_groups_edit_success'));
        });
    }

    public function deleteFilterGroup(FilterGroup $filterGroup): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($filterGroup) {
            Faqs::query()->where('page_type', $this->contentPageType($filterGroup))->delete();
            SeoText::query()->where('page_type', $this->contentPageType($filterGroup))->delete();
            $filterGroup->delete();

            return ServiceActionResult::make(true, trans('admin.filter_group_delete_success'));
        });
    }

    public function contentPageType(FilterGroup $filterGroup): string
    {
        return $filterGroup->editorialPageType();
    }

    public function getFaqs(FilterGroup $filterGroup): Collection
    {
        return Faqs::query()
            ->where('page_type', $this->contentPageType($filterGroup))
            ->orderBy('id')
            ->get();
    }

    public function getFaqsForAdmin(FilterGroup $filterGroup): array
    {
        return $this->getFaqs($filterGroup)
            ->map(fn (Faqs $faq): array => [
                'id' => $faq->id,
                'question' => $faq->getTranslations('question'),
                'answer' => $faq->getTranslations('answer'),
            ])
            ->all();
    }

    /** @return array{title: array<string, string>, content: array<string, string>} */
    public function getSeoTextForAdmin(FilterGroup $filterGroup): array
    {
        $rows = SeoText::query()
            ->where('page_type', $this->contentPageType($filterGroup))
            ->get()
            ->keyBy('language');

        return [
            'title' => collect(['uk', 'ru'])->mapWithKeys(fn (string $locale): array => [
                $locale => (string) ($rows->get($locale)?->title ?? ''),
            ])->all(),
            'content' => collect(['uk', 'ru'])->mapWithKeys(fn (string $locale): array => [
                $locale => (string) ($rows->get($locale)?->content ?? ''),
            ])->all(),
        ];
    }

    /** @return array{title: ?string, content: ?string} */
    public function getSeoTextByLanguage(FilterGroup $filterGroup, string $locale): array
    {
        $row = SeoText::query()
            ->where('page_type', $this->contentPageType($filterGroup))
            ->where('language', $locale)
            ->first();

        return [
            'title' => $row?->title,
            'content' => $row?->content,
        ];
    }

    private function syncEditorialContent(FilterGroup $filterGroup, FilterGroupEditDTO $request): void
    {
        $this->syncFaqs($this->contentPageType($filterGroup), $request->faqs);

        SeoText::updateSeoText(
            $this->contentPageType($filterGroup),
            $request->seoTitle ?? ['uk' => '', 'ru' => ''],
            $request->seoText ?? ['uk' => '', 'ru' => ''],
        );
    }

    public function buildFilterArrayByFilterGroup(FilterGroup $filterGroup): array
    {
        $filtersList = [];

        if ($filterGroup->filters['price_from']) {
            $filtersList['price_from'] = $filterGroup->filters['price_from'];
        }

        if ($filterGroup->filters['price_to']) {
            $filtersList['price_to'] = $filterGroup->filters['price_to'];
        }

        if (isset($filterGroup->filters['custom_fields'])) {
            $productFields = ProductField::whereIn('id', array_column($filterGroup->filters['custom_fields'], 'id'))->get();
            $productFieldOptions = ProductFieldOption::whereIn('product_field_id', array_column($filterGroup->filters['custom_fields'], 'id'))->get();
            foreach ($filterGroup->filters['custom_fields'] as $customField) {
                $customFieldModel = $productFields->where('id', $customField['id'])->first();
                if ($customFieldModel) {
                    if ($customFieldModel->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                        $filtersList[$customFieldModel->slug] = $productFieldOptions->where('product_field_id', $customField['id'])->whereIn('id', $customField['value'])->pluck('slug')->toArray();
                    } else {
                        $filtersList[$customFieldModel->slug] = $customField['value'];
                    }

                }

            }
        }

        if ($filterGroup->filters['color_ids']) {
            $colors = Color::select(['id', 'slug'])->whereIn('id', $filterGroup->filters['color_ids'])->get()->pluck('slug')->toArray();
            $filtersList['color'] = $colors;
        }

        if ($filterGroup->filters['brand_ids']) {
            $colors = Brand::select(['id', 'slug'])->whereIn('id', $filterGroup->filters['brand_ids'])->get()->pluck('slug')->toArray();
            $filtersList['brand'] = $colors;
        }

        if ($filterGroup->filters['country_ids']) {
            $colors = Country::select(['id', 'code'])->whereIn('id', $filterGroup->filters['country_ids'])->get()->pluck('code')->toArray();
            $filtersList['country'] = $colors;
        }

        if ($filterGroup->filters['length_from']) {
            $filtersList['product_length_from'] = $filterGroup->filters['length_from'];
        }

        if ($filterGroup->filters['length_to']) {
            $filtersList['product_length_to'] = $filterGroup->filters['length_to'];
        }

        if ($filterGroup->filters['length_options']) {
            $sizeOptions = ProductTypeSizeOption::select(['id', 'slug'])->whereIn('id', $filterGroup->filters['length_options'])->get()->pluck('slug')->toArray();
            $filtersList['product_length'] = $sizeOptions;
        }

        if ($filterGroup->filters['width_from']) {
            $filtersList['product_width_from'] = $filterGroup->filters['width_from'];
        }

        if ($filterGroup->filters['width_to']) {
            $filtersList['product_width_to'] = $filterGroup->filters['width_to'];
        }

        if ($filterGroup->filters['width_options']) {
            $sizeOptions = ProductTypeSizeOption::select(['id', 'slug'])->whereIn('id', $filterGroup->filters['width_options'])->get()->pluck('slug')->toArray();
            $filtersList['product_width'] = $sizeOptions;
        }

        if ($filterGroup->filters['height_from']) {
            $filtersList['product_height_from'] = $filterGroup->filters['height_from'];
        }

        if ($filterGroup->filters['height_to']) {
            $filtersList['product_height_to'] = $filterGroup->filters['height_to'];
        }

        if ($filterGroup->filters['height_options']) {
            $sizeOptions = ProductTypeSizeOption::select(['id', 'slug'])->whereIn('id', $filterGroup->filters['height_options'])->get()->pluck('slug')->toArray();
            $filtersList['product_height'] = $sizeOptions;
        }

        return $filtersList;
    }
}
