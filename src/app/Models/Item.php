<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * 書き換え不可な属性の配列
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'user_id',
    ];

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

        public function categories()
    {
        return $this->belongsToMany(Category::class,'category_item','item_id','category_id');
    }

    public function likes()
    {
    return $this->belongsToMany(User::class, 'likes', 'item_id', 'user_id');
    }

    public function isLikedBy($user): bool
    {
    // もし$userがnullなら(未ログイン)、falseを返す
        if ($user === null) {
        return false;
        }
    // この商品のいいねリストの中に、引数で渡されたユーザーのIDが存在するかどうかをチェック
    return $this->likes()->where('user_id', $user->id)->exists();
    }
}
