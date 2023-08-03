<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReactiveForm extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly string $action,
        public readonly string $method,
        public readonly ?string $enctype = null,
    )
    { }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.reactive-form', [
            'action' => $this->action,
            'method' => $this->method,
            'enctype' => $this->enctype,
        ]);
    }
}
