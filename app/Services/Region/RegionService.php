<?php

namespace App\Services\Region;

use App\Models\Region;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;

class RegionService extends BaseService
{
    public function getRegions(): Collection
    {
        return Region::orderBy('name->' . app()->getLocale())->get();
    }
}
