<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantRequest extends Model
{
    use HasFactory;

    protected $table = 'requests'; // نحدد اسم الجدول يدوياً

    protected $fillable = [
        'land_id',
        'status',
        'notes',
        'area',
        'rejection_reason',
    ];

    // علاقات (اختياري):
    public function land()
    {
        return $this->belongsTo(Land::class);
    }
}
