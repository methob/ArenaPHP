<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 27/03/2018
 * Time: 11:29
 */

namespace App\Http\Controllers;


use App\FaqQuestions;
use Illuminate\Http\Request;

class FaqController extends Controller
{

    protected function show(Request $request)
    {

        $faq = null;

        switch ($request['type']) {
            case 'shop':
                $faq = FaqQuestions::where(['type' => 1])->get();
                break;
            case 'baby':
                $faq = FaqQuestions::where(['type' => 2])->get();
                break;
            case 'app':
                $faq = FaqQuestions::where(['type' => 3])->get();
                break;
            default:
                $faq = FaqQuestions::all();
        }

        return $this->withoutEncryptData($faq);
    }

}