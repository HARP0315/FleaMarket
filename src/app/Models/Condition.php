<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
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

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
