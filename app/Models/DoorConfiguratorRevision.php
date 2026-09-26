<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoorConfiguratorRevision extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
