<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Land extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'location_name',
        'latitude',
        'longitude',
        'total_area',
        'land_type',
        'soil_type',
        'status',
        'description',
        'water_source',
        'image',
        'required_area',
        'estate_number',
        'id_number'
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }


    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
