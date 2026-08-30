<?php

namespace App\Http\Requests\Admin\CatalogMenu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCatalogMenuContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cards' => ['nullable', 'array'],
            'cards.*.enabled' => ['required', 'boolean'],
            'cards.*.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'columns' => ['nullable', 'array', 'max:6'],
            'columns.*.title' => ['nullable', 'array'],
            'columns.*.title.uk' => ['nullable', 'string', 'max:120'],
            'columns.*.title.ru' => ['nullable', 'string', 'max:120'],
            'columns.*.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'columns.*.items' => ['nullable', 'array', 'max:30'],
            'columns.*.items.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'columns.*.items.*.label' => ['nullable', 'array'],
            'columns.*.items.*.label.uk' => ['nullable', 'string', 'max:160'],
            'columns.*.items.*.label.ru' => ['nullable', 'string', 'max:160'],
            'columns.*.items.*.url' => ['nullable', 'array'],
            'columns.*.items.*.url.uk' => ['nullable', 'string', 'max:2048'],
            'columns.*.items.*.url.ru' => ['nullable', 'string', 'max:2048'],
            'columns.*.items.*.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $productType = $this->route('productType');
            $allowedCategoryIds = $productType?->categories()->pluck('id')->map(fn ($id) => (int) $id) ?? collect();

            foreach ($this->input('columns', []) as $columnIndex => $column) {
                foreach ($column['items'] ?? [] as $itemIndex => $item) {
                    if (! empty($item['category_id'])) {
                        if (! $allowedCategoryIds->contains((int) $item['category_id'])) {
                            $validator->errors()->add(
                                "columns.$columnIndex.items.$itemIndex.category_id",
                                trans('admin.catalog_menu_invalid_category'),
                            );
                        }

                        continue;
                    }

                    foreach (['uk', 'ru'] as $locale) {
                        $hasLabel = trim((string) ($item['label'][$locale] ?? '')) !== '';
                        $url = trim((string) ($item['url'][$locale] ?? ''));
                        $hasUrl = $url !== '';

                        if ($hasLabel xor $hasUrl) {
                            $validator->errors()->add(
                                "columns.$columnIndex.items.$itemIndex.label.$locale",
                                trans('admin.catalog_menu_custom_link_incomplete'),
                            );
                        }

                        if ($hasUrl && ! $this->isSafeMenuUrl($url)) {
                            $validator->errors()->add(
                                "columns.$columnIndex.items.$itemIndex.url.$locale",
                                trans('admin.catalog_menu_invalid_url'),
                            );
                        }
                    }
                }
            }
        });
    }

    private function isSafeMenuUrl(string $url): bool
    {
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        if (str_starts_with($url, '#')) {
            return true;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
