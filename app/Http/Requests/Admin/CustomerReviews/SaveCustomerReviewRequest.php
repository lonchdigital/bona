<?php

namespace App\Http\Requests\Admin\CustomerReviews;

use App\DataClasses\ProductReviewStatusesDataClass;
use App\Models\CustomerReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomerReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => [
                'required',
                Rule::in([
                    CustomerReview::SOURCE_GOOGLE,
                    CustomerReview::SOURCE_MANUAL,
                    CustomerReview::SOURCE_WEBSITE,
                ]),
            ],
            'source_url' => [
                'nullable',
                'required_if:source,'.CustomerReview::SOURCE_GOOGLE,
                'url:http,https',
                'max:2048',
            ],
            'author_avatar_url' => ['nullable', 'url:http,https', 'max:2048'],
            'author_name' => ['required', 'string', 'max:121'],
            'author_name_translations' => ['nullable', 'array'],
            'author_name_translations.uk' => ['nullable', 'string', 'max:121'],
            'author_name_translations.ru' => ['nullable', 'string', 'max:121'],
            'phone' => ['nullable', 'string', 'max:19'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:5000'],
            'review_translations' => ['nullable', 'array'],
            'review_translations.uk' => ['nullable', 'string', 'max:5000'],
            'review_translations.ru' => ['nullable', 'string', 'max:5000'],
            'status_id' => [
                'required',
                'integer',
                Rule::in(ProductReviewStatusesDataClass::get()->pluck('id')->all()),
            ],
            'reviewed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'source_published_label' => ['nullable', 'string', 'max:80'],
            'locale' => ['nullable', Rule::in(['uk', 'ru'])],
        ];
    }
}
