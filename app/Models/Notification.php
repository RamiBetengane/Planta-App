<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'read_status',
        'user_id', // التأكد من إضافة user_id في الـ fillable
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // علاقة من نوع belongsTo مع جدول users
    }
}
