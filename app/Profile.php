<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'gender', 'city_id', 'state_id', 'neighborhood'
    ];

    protected $hidden = [
        'id'
    ];
}