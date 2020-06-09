<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 20/03/2018
 * Time: 17:34
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserNews extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'is_like', 'is_share', 'user_id', 'shop_news_id'
    ];

    protected $hidden = [
        'user_id', 'shop_id', 'id'
    ];
}