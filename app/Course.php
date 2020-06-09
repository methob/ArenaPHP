<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 22/03/2018
 * Time: 14:17
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title', 'description', 'value', 'image', 'pdf'
    ];
}