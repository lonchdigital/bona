<?php

namespace App\Http\Requests\Admin\CatalogMenu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogMenuOverviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'configurations' => ['required', 'array'],
            'configurations.*.is_visible' => ['required', 'boolean'],
            'configurations.*.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'configurations.*.show_in_header' => ['required', 'boolean'],
            'configurations.*.header_order' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }
}
