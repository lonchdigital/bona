<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'search_count' => 'integer',
        'results_count' => 'integer',
        'first_searched_at' => 'datetime',
        'last_searched_at' => 'datetime',
    ];
}
