<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 21/03/2018
 * Time: 17:14
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ShopNews extends Model
{

    public function shops()
    {
        $this->hasManyThrough(UserShop::class, User::class, 'user');
    }

}