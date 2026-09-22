<?php

namespace App\Models;

use App\DataClasses\ProductReviewStatusesDataClass;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_GOOGLE = 'google';

    public const SOURCE_MANUAL = 'manual';

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'reviewed_at' => 'date',
        'rating' => 'integer',
        'status_id' => 'integer',
        'author_name_translations' => 'array',
        'review_translations' => 'array',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status_id', ProductReviewStatusesDataClass::STATUS_APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->status_id === ProductReviewStatusesDataClass::STATUS_APPROVED;
    }

    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => $this->localizedValue(
            $this->author_name_translations,
            $this->author_name,
        ));
    }

    protected function date(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->reviewed_at) {
                return $this->reviewed_at->toDateString();
            }

            // Google only exposes a relative label in the manual import flow.
            // Do not present the website publication timestamp as the review date.
            if ($this->source === self::SOURCE_GOOGLE) {
                return null;
            }

            return ($this->published_at ?: $this->created_at)?->toDateString();
        });
    }

    protected function url(): Attribute
    {
        return Attribute::make(get: fn () => $this->source_url);
    }

    protected function displayReview(): Attribute
    {
        return Attribute::make(get: fn () => $this->localizedValue(
            $this->review_translations,
            $this->review,
        ));
    }

    private function localizedValue(?array $translations, ?string $fallback): string
    {
        $translations ??= [];

        return trim((string) ($translations[app()->getLocale()]
            ?? $fallback
            ?? collect($translations)->first(fn (mixed $value) => filled($value))
            ?? ''));
    }
}
