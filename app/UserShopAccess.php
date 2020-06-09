<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 12/04/2018
 * Time: 22:45
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserShopAccess extends Model
{
    protected $fillable = [
        'user_id', 'shop_id'
    ];
}