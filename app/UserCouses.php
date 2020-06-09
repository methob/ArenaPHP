<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 22/03/2018
 * Time: 16:24
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCouses extends Model
{

    protected $table = 'user_courses';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'course_id'
    ];

//    protected $hidden = [
//        'user_id', 'shop_id', 'id'
//    ];
}