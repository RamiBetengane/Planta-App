<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'scientific_name',
        'common_name',
        'description',
        'water_requirements',
        'sun_requirements',
        'suitable_soil_types',
        'co2_absorption',
        'cancer_risk_impact',
        'growth_min_months',
        'growth_max_months',
        'required_area',
    ];


    public function requests()
    {
        return $this->belongsToMany(Request::class, 'plant_request')->withPivot('quantity');
    }

}
