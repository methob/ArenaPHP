<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 14/03/2018
 * Time: 09:23
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $hidden = [
        'updated_at', 'created_at'
    ];
}