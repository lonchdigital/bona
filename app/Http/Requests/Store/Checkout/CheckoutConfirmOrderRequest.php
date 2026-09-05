<?php

namespace App\Http\Requests\Store\Checkout;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Rules\PhoneNumberLengthRule;
use App\Services\Order\DTO\CheckoutConfirmOrderDTO;
use App\Support\Payment\InstallmentPeriods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CheckoutConfirmOrderRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        if (Auth::check() || ! $this->filled('full_name')) {
            return;
        }

        $fullName = preg_replace('/\s+/u', ' ', trim((string) $this->input('full_name')));
        [$firstName, $lastName] = array_pad(preg_split('/\s+/u', $fullName, 2) ?: [], 2, '');

        $this->merge([
            'full_name' => $fullName,
            'first_name' => $this->filled('first_name') ? $this->input('first_name') : $firstName,
            'last_name' => $this->filled('last_name') ? $this->input('last_name') : $lastName,
        ]);
    }

    public function rules(): array
    {
        $isAuthUser = Auth::user();

        $rules = [
            'delivery_type_id' => [
                'required',
                'int',
                'in:'.DeliveryTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'payment_type_id' => [
                'required',
                'int',
                'in:'.PaymentTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'recipient_type_id' => [
                'required',
                'int',
                'in:'.RecipientTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'comment' => [
                'nullable',
                'string',
            ],
            'agreement' => [
                'bool',
                'required',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        $fail(trans('base.you_have_to_agree_with_policy'));
                    }
                },
            ],
        ];

        if (! $isAuthUser) {
            $rules['full_name'] = [
                'nullable',
                'string',
                'max:201',
                'regex:/^[\pL\pM\'\x{2019}\-\s]+$/u',
            ];

            $rules['first_name'] = [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\pM\'\x{2019}\-\s]+$/u',
            ];

            $rules['last_name'] = [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\pM\'\x{2019}\-\s]+$/u',
            ];

            $rules['phone'] = [
                'required',
                new PhoneNumberLengthRule(12),
            ];

            $rules['email'] = [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ];
        }

        if ($this->input('delivery_type_id') == DeliveryTypesDataClass::ADDRESS_DELIVERY) {
            $rules['region_id'] = [
                'required',
                'integer',
                'exists:regions,id',
            ];

            $rules['city'] = [
                'required',
                'string',
                'max:150',
            ];

            $rules['district'] = [
                'required',
                'string',
                'max:150',
            ];

            $rules['street'] = [
                'required',
                'string',
                'max:180',
            ];

            $rules['building_number'] = [
                'required',
                'string',
                'max:30',
            ];

            $rules['apartment_number'] = [
                'nullable',
                'string',
                'max:30',
            ];

            $rules['floor_number'] = [
                'nullable',
                'string',
                'max:20',
            ];

            $rules['has_elevator'] = [
                'nullable',
            ];

            $rules['save_delivery_address'] = [
                'nullable',
            ];

            /*$rules['delivery_date'] = [
                'required',
                'date_format:d/m/Y',
            ];*/

            /*$rules['delivery_time_id'] = [
                'required',
                'int',
            ];*/
        } elseif ($this->input('delivery_type_id') == DeliveryTypesDataClass::NP_DELIVERY) {
            $rules['np_city'] = [
                'required',
                'string',
            ];

            $rules['np_department'] = [
                'required',
                'string',
            ];
        } elseif ($this->input('delivery_type_id') == DeliveryTypesDataClass::SAT_DELIVERY) {
            $rules['sat_city'] = [
                'required',
                'string',
            ];

            $rules['sat_department'] = [
                'required',
                'string',
            ];
        }

        if ($this->input('recipient_type_id') == RecipientTypesDataClass::RECIPIENT_CUSTOM) {
            $rules['custom_first_name'] = [
                'required',
                'string',
            ];

            $rules['custom_last_name'] = [
                'required',
                'string',
            ];

            $rules['custom_phone'] = [
                'required',
                new PhoneNumberLengthRule(12),
            ];

            $rules['custom_email'] = [
                'required',
                'email',
            ];
        }

        if (
            in_array(
                $this->input('payment_type_id'),
                [PaymentTypesDataClass::CARD_PAYMENT_PAYPART]
            )) {
            $rules['payment_period'] = [
                'required',
                'integer',
                // Only what the shop actually offers: this used to accept any
                // integer at all, so nothing stopped a request naming twenty
                // five payments against a form that shows six.
                Rule::in(InstallmentPeriods::for('privatbank')),
            ];
        }

        if (
            in_array(
                $this->input('payment_type_id'),
                [PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK]
            )) {
            $rules['mono_payment_period'] = [
                'required',
                'integer',
                Rule::in(InstallmentPeriods::for('monobank')),
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'full_name' => mb_strtolower(trans('base.checkout_full_name')),
            'first_name' => mb_strtolower(trans('base.name')),
            'last_name' => mb_strtolower(trans('base.last_name')),
            'phone' => mb_strtolower(trans('base.phone')),
            'email' => mb_strtolower(trans('base.email')),
            'region_id' => mb_strtolower(trans('base.region')),
            'district' => mb_strtolower(trans('base.district')),
            'sat_district' => mb_strtolower(trans('base.district')),
            'city' => mb_strtolower(trans('base.city')),
            'sat_city' => mb_strtolower(trans('base.city')),
            'street' => mb_strtolower(trans('base.checkout_street')),
            'building_number' => mb_strtolower(trans('base.checkout_building_number')),
            'apartment_number' => mb_strtolower(trans('base.checkout_apartment_number')),
            'floor_number' => mb_strtolower(trans('base.checkout_floor_number')),
            'delivery_date' => mb_strtolower(trans('base.checkout_delivery_date')),
            'custom_first_name' => mb_strtolower(trans('base.name')),
            'custom_last_name' => mb_strtolower(trans('base.last_name')),
            'custom_phone' => mb_strtolower(trans('base.phone')),
            'custom_email' => mb_strtolower(trans('base.email')),
            'np_city' => mb_strtolower(trans('base.np_city')),
            'np_department' => mb_strtolower(trans('base.np_department')),
            'meest_city' => mb_strtolower(trans('base.np_city')),
            'meest_department' => mb_strtolower(trans('base.np_department')),
            'payment_period' => mb_strtolower(trans('base.payment_period')),
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => trans('base.checkout_email_registered_error', [
                'email' => trim((string) $this->input('email')),
            ]),
        ];
    }

    public function toDTO(): CheckoutConfirmOrderDTO
    {
        return new CheckoutConfirmOrderDTO(
            $this->input('first_name'),
            $this->input('last_name'),
            $this->input('phone'),
            $this->input('email'),
            $this->input('delivery_type_id'),
            $this->input('payment_type_id'),
            $this->input('region_id'),
            $this->input('district'),
            $this->input('city'),
            $this->input('sat_city'),
            $this->input('sat_department'),
            $this->input('street'),
            $this->input('building_number'),
            $this->input('apartment_number'),
            $this->input('floor_number'),
            //            $this->input('has_elevator'),
            //            $this->input('save_delivery_address'),
            $this->input('delivery_date'),
            $this->input('delivery_time_id'),
            $this->input('recipient_type_id'),
            $this->input('custom_first_name'),
            $this->input('custom_last_name'),
            $this->input('custom_phone'),
            $this->input('custom_email'),
            $this->input('comment'),
            $this->input('np_city'),
            $this->input('np_department'),
            $this->input('meest_city'),
            $this->input('meest_department'),
            $this->input('payment_period')
        );
    }
}
