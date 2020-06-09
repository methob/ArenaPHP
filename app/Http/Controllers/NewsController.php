<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 20/03/2018
 * Time: 17:09
 */

namespace App\Http\Controllers;


use App\News;
use App\UserNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{

    private function validator($data)
    {
        return Validator::make($data, [
            'id' => 'required'
        ]);
    }

    protected function show(Request $request)
    {
//        DB::enableQueryLog();

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $news = DB::table('user_shop')
            ->join('shop', 'user_shop.shop_id', '=', 'shop.id')
            ->join('shop_news', 'user_shop.shop_id', '=', 'shop_news.shop_id')
            ->leftJoin('user_news', 'shop_news.id', '=', 'user_news.shop_news_id')
            ->select('shop.nickname', 'user_news.is_like', 'user_news.is_share', 'shop_news.title',
                'shop_news.description', 'shop_news.id',
                DB::raw('count(CASE WHEN user_news.is_like = true THEN 1 END) AS count_like'),
                DB::raw('count(CASE WHEN user_news.is_share = true THEN 1 END) AS count_share'))
            ->where(['user_shop.user_id' => $request['id'], 'user_shop.is_follow' => true])
            ->groupBy('id')
            ->get();

//        dd(DB::getQueryLog());

        foreach ($news as $new) {
            $new->photos = DB::table('shop_news_photos')
                ->select('shop_news_photos.filename')
                ->where('shop_news_photos.shop_news_id', '=', $new->id)->get();

            $new->photos = array_map(function($obj){
                return url("storage/app/public/images/" . $obj->filename);
                }, $new->photos->toArray());
        }

        return $this->withoutEncryptData($news);
    }

    private function validatorShareAndLike($data)
    {

        return Validator::make($data, [
            'id' => 'required',
            'news_id' => 'required',
        ]);
    }

    protected function share(Request $request)
    {
        $validator = $this->validatorShareAndLike($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shop = UserNews::firstOrNew(['user_id' => $request['id'], 'shop_news_id' => $request['news_id']]);
        $shop->is_share = true;
        $shop->save();

        $response = [
            'message' => "Requisição realizada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function like(Request $request)
    {

        $validator = $this->validatorShareAndLike($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shop = UserNews::firstOrNew(['user_id' => $request['id'], 'shop_news_id' => $request['news_id']]);
        $shop->is_like = !$shop->is_like;
        $shop->save();

        $response = [
            'message' => "Requisição realizada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function all(Request $request)
    {
        $news = News::all();

        foreach ($news as $new) {

            $new->photos = DB::table('shop_news_photos')
                ->select('shop_news_photos.filename')
                ->where('shop_news_photos.shop_news_id', '=', $new->id)->get();

            $new->photos = array_map(function($obj){
                return asset($obj->filename);
            }, $new->photos->toArray());
        }

        return $this->withoutEncryptData($news);
    }

    protected function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'news_id' => 'required',
            'description' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $new = News::find($request['news_id']);

        $new->description = $request['description'];
        $new->title = $request['title'];
        $new->save();

        $response = [
            'message' => "Novidade atualiza com sucesso!"
        ];

        return $this->withoutEncryptData($response);

    }

    protected function getNewsByShop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->withoutEncryptData(News::where(['shop_id' => $request['shop_id']])->get());
    }

    protected function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $news = News::create(['title' => $request['title'],
            'description' => $request['description'], 'shop_id' => $request['shop_id']]);

        if ($request['images'] != null) {
            foreach ($request['images'] as $item) {

                $filename = $this->storeFile($item);
                DB::table('shop_news_photos')->insert(['shop_news_id' => $news->id, 'filename' => $filename]);
            }
        }

        $response = [
            'message' => "Novidade criada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    private function storeFile($image)
    {
        $filename = $image->store('images', 'public');
        $filename = str_replace('/images', '', $filename);
        return $filename;
    }
}
