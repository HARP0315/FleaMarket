<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 書き換え不可な属性の配列
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function shippingAddress()
    {
        return $this->hasMany(Address::class);
    }

        public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
    // ユーザーがいいねしたItemを、likesテーブル経由で取得する
        return $this->belongsToMany(Item::class, 'likes', 'user_id', 'item_id');
    }
}
