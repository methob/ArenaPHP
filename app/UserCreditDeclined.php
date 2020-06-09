<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 20/03/2018
 * Time: 15:29
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserCreditDeclined extends Model
{
    public $timestamps = false;

    protected $table = 'user_credits_declined';

    protected $fillable = [
        'message', 'answer_option', 'user_credit'
    ];
}