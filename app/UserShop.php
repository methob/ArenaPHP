<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 19/03/2018
 * Time: 15:10
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserShop extends Model
{
    public $timestamps = false;

    protected $table = 'user_shop';

    protected $fillable = [
        'user_id', 'shop_id', 'stars', 'current_credit'
    ];

    protected $hidden = [
        'user_id', 'shop_id', 'id'
    ];

    public function shop() {
        $this->belongsTo(Shop::class, 'shop_id');
    }

    public function user() {
        $this->belongsTo(User::class, 'user_id');
    }
}