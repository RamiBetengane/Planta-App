<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doner extends Model
{
    protected $fillable = [
        'user_id',
        'tax_id',
        'donor_type',
    ];

    // العلاقة مع User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
