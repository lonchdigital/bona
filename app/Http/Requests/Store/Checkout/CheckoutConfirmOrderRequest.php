<?php

namespace App\Http\Requests\Store\Checkout;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Rules\PhoneNumberLengthRule;
use App\Services\Order\DTO\CheckoutConfirmOrderDTO;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Translation\t;

class CheckoutConfirmOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        $isAuthUser = Auth::user();

        $rules = [
            'delivery_type_id' => [
                'required',
                'int',
                'in:' . DeliveryTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'payment_type_id' => [
                'required',
                'int',
                'in:' . PaymentTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'recipient_type_id' => [
                'required',
                'int',
                'in:' . RecipientTypesDataClass::get()->pluck('id')->implode(','),
            ],
            'comment' => [
                'nullable',
                'string',
            ],
            'agreement' => [
                'bool',
                'required',
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        $fail(trans('base.you_have_to_agree_with_policy'));
                    }
                },
            ]
        ];

        if (!$isAuthUser) {
            $rules['first_name'] = [
                'required',
                'string',
                'alpha'
            ];

            $rules['last_name'] = [
                'required',
                'string',
                'alpha'
            ];

            $rules['phone'] = [
                'required',
                new PhoneNumberLengthRule(12),
            ];

            $rules['email'] = [
                'required',
                'email',
//                'unique:users,email'
            ];
        }

        if ($this->input('delivery_type_id') == DeliveryTypesDataClass::ADDRESS_DELIVERY) {
             $rules['region_id'] = [
                 'required',
                 'integer',
                 'exists:regions,id'
             ];

             $rules['city'] = [
                 'required',
                 'string'
             ];

             $rules['district'] = [
                 'required',
                 'string',
                 'alpha'
             ];

             $rules['street'] = [
                 'required',
                 'string',
                 'alpha'
             ];

            $rules['building_number'] = [
                'required',
                'string',
                'numeric'
            ];

            $rules['apartment_number'] = [
                'nullable',
                'string',
                'numeric'
            ];

            $rules['floor_number'] = [
                'nullable',
                'string',
                'numeric'
            ];

            $rules['has_elevator'] = [
                'nullable'
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
                'string'
            ];

            $rules['custom_last_name'] = [
                'required',
                'string'
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

        return $rules;
    }


    public function attributes(): array
    {
        return [
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
        );
    }
}
