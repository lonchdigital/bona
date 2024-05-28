<?php

namespace App\Services\Color;

use App\Services\Base\BaseService;
use App\Services\Color\DTO\FilterColorAdminDTO;
use Illuminate\Database\Eloquent\Builder;

class ColorFiltersAdminService extends BaseService
{
    public function handleColorFilters(FilterColorAdminDTO $request, Builder $query): Builder
    {
        if ($request->search) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            });
        }


        return $query;
    }


}
