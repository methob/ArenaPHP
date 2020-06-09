<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 20/03/2018
 * Time: 14:01
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserCredit extends Model
{
    public $timestamps = false;

    protected $table = 'user_credits';

    protected $fillable = [
        'is_declined', 'value', 'user_shop_id'
    ];

    protected $hidden = [
        'is_declined', 'user_shop_id'
    ];
}