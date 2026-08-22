<?php

namespace App\Models;

use App\DataClasses\ProductReviewStatusesDataClass;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'rating' => 'integer',
        'status_id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_id', ProductReviewStatusesDataClass::STATUS_APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->status_id === ProductReviewStatusesDataClass::STATUS_APPROVED;
    }

    /**
     * The date a review is shown under, falling back to when it arrived.
     */
    public function publishedDate()
    {
        return $this->published_at ?: $this->created_at;
    }
}
