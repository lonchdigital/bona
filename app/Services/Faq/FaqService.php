<?php

namespace App\Services\Faq;

use App\Helpers\MultiLangRoute;
use App\Models\Faqs;
use App\Models\ProductType;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;

/**
 * The FAQ hub gathers what the site already knows.
 *
 * Questions are stored against a page_type, which the home page and every
 * product type already use. The hub reads all of them and groups them, so a
 * question added to a category in the admin panel turns up here on its own,
 * with nothing to keep in step by hand.
 */
class FaqService extends BaseService
{
    public function getGroupedFaqs(): Collection
    {
        $faqs = Faqs::orderBy('id')->get()->groupBy('page_type');

        if ($faqs->isEmpty()) {
            return collect();
        }

        $groups = collect();

        // The general questions on the home page open the page.
        $generalKey = config('constants.HOMEPAGE_TYPE');

        if ($faqs->has($generalKey)) {
            $groups->push([
                'key' => $generalKey,
                'title' => trans('base.faq_general_group'),
                'url' => null,
                'items' => $faqs->get($generalKey),
            ]);
        }

        // Then one group per product type, in the order the catalogue uses,
        // each linking back to the category it answers questions about.
        foreach (ProductType::orderBy('id')->get() as $productType) {
            if (!$faqs->has($productType->slug)) {
                continue;
            }

            $groups->push([
                'key' => $productType->slug,
                'title' => (string) $productType->name,
                'url' => MultiLangRoute::getMultiLangRoute(
                    'store.catalog.page',
                    ['productTypeSlug' => $productType->slug]
                ),
                'items' => $faqs->get($productType->slug),
            ]);
        }

        // Anything stored against a page_type nothing recognises still belongs
        // on the page rather than quietly disappearing from it.
        $known = $groups->pluck('key')->all();

        foreach ($faqs as $pageType => $items) {
            if (in_array($pageType, $known, true)) {
                continue;
            }

            $groups->push([
                'key' => $pageType,
                'title' => trans('base.faq_other_group'),
                'url' => null,
                'items' => $items,
            ]);
        }

        return $groups;
    }

    public function countQuestions(Collection $groups): int
    {
        return $groups->sum(fn (array $group) => $group['items']->count());
    }
}
