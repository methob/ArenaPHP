<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 23/03/2018
 * Time: 16:58
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ProductsEvaluate extends Model
{
    protected $table = 'products_evaluate';

    protected $fillable = [
        'shop_id', 'user_id', 'name', 'is_donate', 'status', 'code','value'
    ];

    protected $hidden = [
        'shop_id', 'user_id', 'is_donate'
    ];

}