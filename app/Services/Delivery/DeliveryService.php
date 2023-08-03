<?php

namespace App\Services\Delivery;

use App\Intergations\MeestExpress\MeestExpress;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use LisDev\Delivery\NovaPoshtaApi2;

class DeliveryService extends BaseService
{
    public function getNpCities(string $query = ''): Collection
    {
        try {
            $np = new NovaPoshtaApi2(
                config('delivery.np_api_key'),
                app()->getLocale(),
                true,
            );

            return collect($np->getCities(1, $query)['data'])->map(function ($city) {
                $city['value'] = $city['Ref'];
                $city['text'] = app()->getLocale() === 'ru' ? $city['DescriptionRu'] : $city['Description'] . ' (' . (app()->getLocale() === 'ru' ? $city['AreaDescriptionRu'] : $city['AreaDescription']) . ' ' . mb_strtolower(trans('base.region')) . ')';

                return $city;
            });
        } catch (\Exception $exception) {
            return collect([]);
        }
    }

    public function getNpCityByRef(string $ref): array
    {
        try {
            $np = new NovaPoshtaApi2(
                config('delivery.np_api_key'),
                app()->getLocale(),
                true,
            );

            return collect($np->getCities(1, '', $ref)['data'])->map(function ($city) {
                $city['value'] = $city['Ref'];
                $city['text'] = app()->getLocale() === 'ru' ? $city['DescriptionRu'] : $city['Description'] . ' (' . (app()->getLocale() === 'ru' ? $city['AreaDescriptionRu'] : $city['AreaDescription']) . ' ' . mb_strtolower(trans('base.region')) . ')';

                return $city;
            })[0];
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getNpDepartments(string $cityRef): Collection
    {
        try {
            $np = new NovaPoshtaApi2(
                config('delivery.np_api_key'),
                app()->getLocale(),
                true,
            );

            return collect($np->getWarehouses($cityRef)['data'])->map(function ($department) {
                $department['value'] = $department['Ref'];
                $department['text'] = app()->getLocale() === 'ru' ? $department['DescriptionRu'] : $department['Description'];

                return $department;
            });
        } catch (\Exception $exception) {
            return collect([]);
        }
    }

    public function getNpDepartmentByRef(string $cityRef, string $ref): array
    {
        try {
            $np = new NovaPoshtaApi2(
                config('delivery.np_api_key'),
                app()->getLocale(),
                true,
            );

            return collect($np->getWarehouses($cityRef)['data'])->map(function ($department) {
                $department['value'] = $department['Ref'];
                $department['text'] = app()->getLocale() === 'ru' ? $department['DescriptionRu'] : $department['Description'];

                return $department;
            })->where('value', $ref)[0];
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getMeestCities(string $query): Collection
    {
        $meest = new MeestExpress(config('delivery.meest_username'), config('delivery.meest_password'));

        return $meest->getCities($query)->map(function ($city) {
            $ruText = $city['cityDescr']['descrRU'] . ' ' . (isset($city['districtDescr']) && $city['districtDescr']['descrRU'] !== $city['cityDescr']['descrRU'] ? $city['districtDescr']['descrRU'] . ' ' . trans('base.district') : '') . ' ' . (isset($city['regionDescr']) ? $city['regionDescr']['descrRU'] . ' ' . trans('base.region') : '');
            $ukText = $city['cityDescr']['descrUA'] . ' ' . (isset($city['districtDescr']) && $city['districtDescr']['descrUA'] !== $city['cityDescr']['descrUA'] ? $city['districtDescr']['descrUA'] . ' ' . trans('base.district') : '') . ' ' . (isset($city['regionDescr']) ? $city['regionDescr']['descrUA'] . ' ' . trans('base.region') : '');
            $city['value'] = $city['cityID'];
            $city['text'] = app()->getLocale() === 'ru' ? $ruText : $ukText;

            return $city;
        });
    }

    public function getMeestCityByRef(string $ref): array
    {
        $meest = new MeestExpress(config('delivery.meest_username'), config('delivery.meest_password'));

        return $meest->getCityByRef($ref)->map(function ($city) {
            $ruText = $city['cityDescr']['descrRU'] . ' ' . (isset($city['districtDescr']) && $city['districtDescr']['descrRU'] !== $city['cityDescr']['descrRU'] ? $city['districtDescr']['descrRU'] . ' ' . trans('base.district') : '') . ' ' . (isset($city['regionDescr']) ? $city['regionDescr']['descrRU'] . ' ' . trans('base.region') : '');
            $ukText = $city['cityDescr']['descrUA'] . ' ' . (isset($city['districtDescr']) && $city['districtDescr']['descrUA'] !== $city['cityDescr']['descrUA'] ? $city['districtDescr']['descrUA'] . ' ' . trans('base.district') : '') . ' ' . (isset($city['regionDescr']) ? $city['regionDescr']['descrUA'] . ' ' . trans('base.region') : '');
            $city['text_ru'] = $ruText;
            $city['text_uk'] = $ukText;
            $city['value'] = $city['cityID'];
            $city['text'] = app()->getLocale() === 'ru' ? $ruText : $ukText;

            return $city;
        })[0];
    }

    public function getMeestDepartments(string $query): Collection
    {
        $meest = new MeestExpress(config('delivery.meest_username'), config('delivery.meest_password'));

        return $meest->getDepartments($query)->map(function ($department) {
            $ruText = trans('base.meest_delivery') . ' ' . $department['branchNumber'] . ' ' . $department['addressDescr']['descrRU'];
            $ukText = trans('base.meest_delivery') . ' ' . $department['branchNumber'] . ' ' . $department['addressDescr']['descrUA'];
            $department['value'] = $department['branchID'];
            $department['text'] = app()->getLocale() === 'ru' ? $ruText : $ukText;
            $department['weightTotalMax'] = $department['branchLimits']['weightTotalMax'];

            return $department;
        })
            ->where('weightTotalMax', '>=', 30)
            ->sortBy('branchNumber');
    }

    public function getMeestDepartmentByRef(string $ref): array
    {
        $meest = new MeestExpress(config('delivery.meest_username'), config('delivery.meest_password'));

        return $meest->getDepartmentByRef($ref)->map(function ($department) {
            $ruText = trans('base.meest_delivery') . ' ' . $department['branchNumber'] . ' ' . $department['addressDescr']['descrRU'];
            $ukText = trans('base.meest_delivery') . ' ' . $department['branchNumber'] . ' ' . $department['addressDescr']['descrUA'];
            $department['text_ru'] = $ruText;
            $department['text_uk'] = $ukText;
            $department['value'] = $department['branchID'];
            $department['text'] = app()->getLocale() === 'ru' ? $ruText : $ukText;
            $department['weightTotalMax'] = $department['branchLimits']['weightTotalMax'];

            return $department;
        })[0];
    }

}
