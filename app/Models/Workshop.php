<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'user_id',
        'years_of_experience',
        'rating',
        'specialization',
        'license_number',
        'workshop_name',
    ];

    // العلاقة مع ال User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
