<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'workshop_id',
        'total_offer_amount',
        'estimation_completion',
        'status',
        'notes'
    ];

    // علاقة مع الـ Tender
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    // علاقة مع الـ Workshop
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }


//
    public function offerDetails()
    {
        return $this->hasMany(OfferDetail::class);
    }

}
