<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 20/03/2018
 * Time: 17:08
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{

    protected $table = 'shop_news';

    protected $fillable = [
        'title', 'description'
    ];

    protected $hidden = [
        'updated_at'
    ];

}