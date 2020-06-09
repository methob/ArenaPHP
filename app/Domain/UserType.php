<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 27/03/2018
 * Time: 13:43
 */

namespace App\Domain;


abstract class UserType
{
    const APP = 1;
    const GENERAL_ADMIN = 2;
    const FRANCHISE_ADMIN = 3;
}