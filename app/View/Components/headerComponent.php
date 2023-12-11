<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class headerComponent extends Component
{

    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $static = true
    )
    {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.header-component', [
            'data' => $this->data,
            'static' => $this->static
        ]);
    }
}
