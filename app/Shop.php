<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 19/03/2018
 * Time: 15:11
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    public $timestamps = false;

    protected $table = 'shop';

    protected $guarded = [];

    protected $hidden = [
        'is_declined', 'user_shop_id'
    ];

    public function userShop()
    {
        $this->hasMany(UserShop::class, 'shop_id');
    }

}