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

    // علاقة BelongsTo مع جدول Land
    public function land()
    {
        return $this->belongsTo(Land::class);
    }
    public function plants()
    {
        return $this->belongsToMany(Plant::class, 'plant_request')->withPivot('quantity');
    }


}
