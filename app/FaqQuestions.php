<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 27/03/2018
 * Time: 11:40
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class FaqQuestions extends Model
{
    protected $table = 'faq_questions';

    protected $fillable = [
        'question', 'answer'
    ];
}