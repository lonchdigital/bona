<?php

namespace App\Models;

use App\DataClasses\ProductReviewStatusesDataClass;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'rating' => 'integer',
        'status_id' => 'integer',
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
        return Attribute::make(get: fn () => $this->author_name);
    }

    protected function date(): Attribute
    {
        return Attribute::make(get: fn () => ($this->published_at ?: $this->created_at)?->toDateString());
    }

    protected function url(): Attribute
    {
        return Attribute::make(get: fn () => null);
    }
}
