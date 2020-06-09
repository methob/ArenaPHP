<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 10/04/2018
 * Time: 22:43
 */

namespace App\Http\Controllers;


use App\Domain\TransationType;
use App\Domain\UserType;
use App\Domain\UserTypeFidelity;
use Illuminate\Support\Facades\DB;
use stdClass;

class DashboardController extends Controller
{

    public function dashboard()
    {
        $response = new stdClass();

        $response->totalAccess = DB::table('user_access')->count();
        $response->users = DB::table('users')->where(['user_type' => UserType::APP])->count();
        $response->sales = DB::table('transactions')->where(['transation_type' => TransationType::OUTPUT])->count();
        $response->babycoins = DB::table('transactions')->where(['transation_type' => TransationType::OUTPUT,
            'is_transaction_in_stars' => true])->count();
        $response->usersOnline = DB::table('user_access')->where(['user_type' => UserType::APP, 'is_online' => true])->count();
        $response->shopCredit = DB::table('shop')->select(['social_name', 'credit'])->get();

        $response->accessByShop = $this->format(DB::table('user_shop_access')
            ->select('shop_id',
                DB::raw('YEAR(created_at) year, 
                MONTH(created_at) month, 
                COUNT(shop_id) as mount'),
                DB::raw('(SELECT nickname FROM shop WHERE id = shop_id) AS name'))
            ->groupBy('year', 'month', 'shop_id')
            ->get());

        $response->avgAcceptance = $this->getAcceptanceAnswer();

        $response->clients = $this->getAvgClients();

        $response->userByState = DB::table('profiles')
            ->join('users', 'users.profile_id', '=', 'profiles.id')
            ->join('states', 'states.id', '=', 'profiles.state_id')
            ->select('states.name', DB::raw('COUNT(users.id) as count'), 'states.id')
            ->groupBy('states.id')
            ->get();

        return $this->withoutEncryptData($response);
    }

    private function getAvgClients()
    {

        $clients = new stdClass();

        $clients->fidelidade = DB::table('user_type_fidelity')->
        select(DB::raw('COUNT(id) as countFidelity'))
            ->where(['user_fidelity' => UserTypeFidelity::FIDELIDADE])
            ->value('countFidelity');

        $clients->arena = DB::table('user_type_fidelity')->
        select(DB::raw('COUNT(id) as countArena'))
            ->where(['user_fidelity' => UserTypeFidelity::CLUBE_ARENA_BABY])
            ->value('countArena');

        return $clients;
    }

    private function getAcceptanceAnswer()
    {

        $countNotAcceptance = DB::table('user_credit_acceptance')
            ->selectRaw(DB::raw('COUNT(id) as count'))
            ->where(['is_accepted' => false])->first();

        $total = DB::table('user_credit_acceptance')->count();

        $response = new stdClass();

        if ($total > 0) {

            $response->avgAcceptance = ($total - $countNotAcceptance->count) / $total;
            $response->avgNotAcceptance = $countNotAcceptance->count / $total;
        }

        return $response;
    }

    private function format($response)
    {

        $hash = [];

        foreach ($response as $item) {

            if (isset($hash[$item->shop_id])) {
                $array = $hash[$item->shop_id];
            } else {
                $array = array();
            }

            array_push($array, $item);
            $hash[$item->shop_id] = $array;
        }

        return array_values($hash);
    }
}