<?php

namespace App\Http\Requests\Admin\Currency;

use App\Http\Requests\BaseRequest;
use App\Models\Currency;
use App\Services\Currency\DTO\EditCurrencyDTO;

class CurrencyEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'is_base' => [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) {
                    $baseCurrency = Currency::where('is_base', true)->first();
                    if ($value) {
                        if (!(($baseCurrency && $this->route('currency') && $this->route('currency')->id === $baseCurrency->id) ||
                        !$baseCurrency)) {
                            $fail(trans('admin.base_currency_already_exists'));
                        }
                    }
                }
            ],
            'rate' => [
                $this->input('is_base') ? 'nullable' : 'required',
                'numeric',
            ],

            'code' => [
                'required',
                'string',
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['name_short.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'code' => mb_strtolower(trans('admin.currency_code')),
            'rate' => mb_strtolower(trans('admin.currency_rate')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.currency_name'), $availableLanguage);
            $attributes['name_short.' . $availableLanguage] = $this->prepareAttribute(trans('admin.currency_name_short'), $availableLanguage);
        }
        return $attributes;
    }

    public function toDTO(): EditCurrencyDTO
    {
        return new EditCurrencyDTO(
            $this->input('name'),
            $this->input('name_short'),
            $this->input('code'),
            $this->input('is_base'),
            $this->input('rate'),
        );
    }
}
