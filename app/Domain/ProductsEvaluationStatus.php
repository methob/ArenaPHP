<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 23/03/2018
 * Time: 17:10
 */

namespace App\Domain;


 abstract class ProductsEvaluationStatus
{
     const PENDING = 1;
     const EVALUATED = 2;
     const REFUSED = 3;
     const RESCUED = 4;
}