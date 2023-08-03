<?php

namespace App\View\Components\Admin;

use Closure;
use App\Models\ProductType;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use App\DataClasses\ProductSizeTypesDataClass;

class SizeFilterBlock extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly string $type,
        public readonly ?ProductType $productType = null,
    ) { }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        //ProductSizeTypesDataClass::LENGTH
        $firstOptionOffset = 0;
        if ($this->type === ProductSizeTypesDataClass::WIDTH) {
            $firstOptionOffset = 1;
        }
        elseif ($this->type === ProductSizeTypesDataClass::HEIGHT) {
            $firstOptionOffset = 2;
        }

        return view('components.admin.size-filter-block', [
            'productType' => $this->productType,
            'type' => $this->type,
            'name' => mb_strtolower($this->type),
            'firstOptionOffset' => $firstOptionOffset,
        ]);
    }
}
