<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenders()
    {
        return $this->hasMany(Tender::class);
    }

}
