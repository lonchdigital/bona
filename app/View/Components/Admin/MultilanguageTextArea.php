<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultilanguageTextArea extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly string $label,
        public readonly string $fieldName,
        public readonly ?array $values,
        public readonly ?bool $isRequired = false,
    ) { }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.multilanguage-text-area', [
            'label' => $this->label,
            'fieldName' => $this->fieldName,
            'errorFieldName' => preg_replace(
                '(\.$)',
                '',
                preg_replace(
                    '(\]\[|\[|\])',
                    '.',
                    $this->fieldName
                )
            ),
            'values' => $this->values,
            'isRequired' => $this->isRequired,
        ]);
    }
}
