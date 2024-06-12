<?php

namespace App\Http\Requests\Store\Checkout;

use App\Rules\PrivatPartialPaymentSignatureCheck;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmPartialOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'storeId' => ['required', 'string'],
            'orderId' => ['required', 'string'],
            'paymentState' => ['required', 'string'],
            'signature' => ['required', 'string', new PrivatPartialPaymentSignatureCheck],
            'message' => ['required ', 'string'],
        ];
    }
}
