<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'plant_request_id',
        'plant_id',
        'unit_cost',
        'total_cost',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function plantRequest()
    {
        return $this->belongsTo(PlantRequest::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
