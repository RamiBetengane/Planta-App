<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'land_id',
        'status',
        'notes',
        'area',
    ];

    public function land()
    {
        return $this->belongsTo(Land::class);
    }

    public function plants()
    {
        return $this->belongsToMany(Plant::class, 'plant_request')->withPivot('quantity');
    }

// 1
    public function tender()
    {
        return $this->hasOne(Tender::class);
    }


    // 2
    public function plantRequests()
    {
        return $this->hasMany(PlantRequest::class,'request_id','id');
    }
// 3
// Request.php
//    public function plant_requests()
//    {
//        return $this->hasMany(PlantRequest::class, 'request_id', 'id');
//    }

}
