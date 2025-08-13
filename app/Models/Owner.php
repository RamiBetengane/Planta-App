<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','estate_number','national_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function lands()
    {
        return $this->hasMany(Land::class);
    }
}
