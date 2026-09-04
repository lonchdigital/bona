<?php

namespace App\Http\Requests\Admin\CatalogMenu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFooterMenusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        foreach (['navigation', 'categories'] as $menu) {
            $rules[$menu] = ['nullable', 'array', 'max:30'];
            $rules["$menu.*.label"] = ['required', 'array'];
            $rules["$menu.*.url"] = ['required', 'array'];
            $rules["$menu.*.is_visible"] = ['required', 'boolean'];
            $rules["$menu.*.sort_order"] = ['required', 'integer', 'min:0', 'max:999'];

            foreach (['uk', 'ru'] as $locale) {
                $rules["$menu.*.label.$locale"] = ['required', 'string', 'max:160'];
                $rules["$menu.*.url.$locale"] = ['required', 'string', 'max:2048'];
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (['navigation', 'categories'] as $menu) {
                foreach ($this->input($menu, []) as $index => $item) {
                    foreach (['uk', 'ru'] as $locale) {
                        $url = trim((string) data_get($item, "url.$locale"));

                        if ($url !== '' && ! $this->isSafeMenuUrl($url)) {
                            $validator->errors()->add(
                                "$menu.$index.url.$locale",
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
        if ((str_starts_with($url, '/') && ! str_starts_with($url, '//')) || str_starts_with($url, '#')) {
            return true;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
