<?php

namespace App\Services\Currency;

use App\Jobs\UpdateProductsPriceByCurrencyRateJob;
use App\Models\Currency;
use App\Models\Product;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Currency\DTO\EditCurrencyDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CurrencyService extends BaseService
{
    public function getCurrencies(): Collection
    {
        return Currency::get();
    }

    public function getCurrenciesPaginated(): LengthAwarePaginator
    {
        return Currency::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getBaseCurrency(): Currency
    {
        $baseCurrency = Currency::where('is_base', true)->first();

        if (!$baseCurrency) {
            return Currency::first();
        }

        return $baseCurrency;
    }

    public function createCurrency(EditCurrencyDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithTryCatch(function () use($request, $creator) {
            Currency::create([
                'creator_id' => $creator->id,
                'name' => $request->name,
                'name_short' => $request->nameShort,
                'code' => $request->code,
                'is_base' => $request->isBase,
                'rate' => $request->rate,
            ]);

            return ServiceActionResult::make(true, trans('admin.currency_add_success'));
        });
    }

    public function editCurrency(Currency $currency, EditCurrencyDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($currency, $request) {
            $currency->update([
                'name' => $request->name,
                'name_short' => $request->nameShort,
                'code' => $request->code,
                'is_base' => $request->isBase,
                'rate' => $request->rate,
            ]);

            UpdateProductsPriceByCurrencyRateJob::dispatchAfterResponse($currency->id);

            return ServiceActionResult::make(true, trans('admin.currency_edit_success'));
        });
    }

    public function deleteCurrency(Currency $currency): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($currency) {
            if (Product::where('price_currency_id', $currency->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.currency_in_use'));
            }

            $currency->delete();

            return ServiceActionResult::make(true, trans('admin.currency_delete_success'));
        });
    }
}
