<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantRequest extends Model
{
    use HasFactory;

 //   protected $table = 'requests';
    protected $table = 'plant_request';  // ✅ الجدول الوسيط الصحيح

    protected $fillable = [
        'land_id',
        'status',
        'notes',
        'area',
        'rejection_reason',
    ];

//
//    public function plant()
//    {
//        return $this->belongsTo(Plant::class, 'plant_id');
//    }

    // PlantRequest.php
    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'id');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }


}
