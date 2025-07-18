<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_request_id',
        'manager_id',
        'creation_date',
        'open_date',
        'close_date',
        'status',
        'technical_requirements',
    ];

    // العلاقات
    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function plantRequest()
    {
        return $this->belongsTo(PlantRequest::class);
    }
}
