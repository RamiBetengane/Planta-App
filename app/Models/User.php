<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens; // لو ستستخدم Sanctum للتوكنات

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable ,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone_number',
        'address',
        'registration_date',
        'user_type',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }
    public function manager()
    {
        return $this->hasOne(Manager::class);
    }
    public function donor()
    {
        return $this->hasOne(Doner::class);
    }
    public function workshop()
    {
        return $this->hasOne(Workshop::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class); // علاقة من نوع hasMany مع جدول notifications
    }

}
