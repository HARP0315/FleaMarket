<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

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
    // この商品のいいねリストの中に、指定ユーザーIDが存在するかどうかチェック
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * conditionを、configファイルの値に変換するためのアクセサ
     */
    protected function conditionContent(): Attribute
    {
        return Attribute::make(
            get: fn () => config('const.conditions.conditions')[$this->condition] ?? '未設定',
        );
    }

    /**
     * 画像のURLを自動的に判断して返すアクセサ
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // $attributes['img'] で、このモデルの'img'カラムの値を取得
                $path = $attributes['img'];

                // もしパスが'http'で始まっていたら、外部URLなのでそのまま返す
                if (Str::startsWith($path, 'http')) {
                    return $path;
                }

                // パスが'items/'や'profiles/'で始まっていたら、それはユーザーがアップロードしたファイル
                if (Str::startsWith($path, 'items/') || Str::startsWith($path, 'profiles/')) {
                    return asset('storage/' . $path);
                }

                // それ以外は、publicフォルダにあるダミー画像と判断
                return asset($path);
            }
        );
    }
}
