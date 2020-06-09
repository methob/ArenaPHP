<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 22/03/2018
 * Time: 14:17
 */

namespace App\Http\Controllers;

use App\Course;
use App\Domain\TransationType;
use App\ShopNews;
use App\Transaction;
use App\User;
use App\UserCouses;
use App\UserNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    private function validator($data)
    {
        return Validator::make($data, [
            'id' => 'required'
        ]);
    }

    private function acquireValidator($data)
    {
        return Validator::make($data, [
            'id' => 'required',
            'course_id' => 'required'
        ]);
    }

    protected function show(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $courses = DB::table('courses')
            ->select('courses.title', 'courses.description', 'courses.title', 'courses.image', 'courses.value',
                'courses.id')
            ->leftJoin('user_courses', function ($join) use ($request) {
                $join->on('courses.id', '!=', 'user_courses.course_id')
                    ->where(['user_courses.user_id' => $request['id']]);
            })
            ->get();

        return $this->withoutEncryptData($courses);
    }

    protected function acquire(Request $request)
    {
        $validator = $this->acquireValidator($request->all());

        if (UserCouses::where(['user_id' => $request['id'],
                'course_id' => $request['course_id']])->first() != null) {

            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => 'Curso já adquirido.'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request['id']);
        $course = Course::find($request['course_id']);

        if ($user->credit < $course->value) {

            return response()->json([
                'message' => 'Você não tem saldo para comprar esse curso.',

            ], 401);

        } else {

            UserCouses::create(['user_id' => $user->id, 'course_id' => $course->id]);
            $user->credit = $user->credit - $course->value;
            $user->save();

            Transaction::create(['value' => $course->value,
                'transation_type' => TransationType::OUTPUT, 'is_transaction_in_stars' => true]);

            $response = [
                'message' => "Curso comprado com sucesso!"
            ];

            return $this->withoutEncryptData($response);
        }
    }

    protected function myCourses(Request $request)
    {

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $courses = DB::table('courses')
            ->select('courses.title', 'courses.description', 'courses.title', 'courses.image', 'courses.value',
                'courses.id')
            ->leftJoin('user_courses', function ($join) use ($request) {
                $join->on('courses.id', '=', 'user_courses.course_id')
                    ->where(['user_courses.user_id' => $request['id']]);
            })
            ->get();

        return $this->withoutEncryptData($courses);
    }

    protected function all(Request $request)
    {
        $courses = DB::table('courses')
            ->leftJoin('user_courses', 'user_courses.course_id', '=', 'courses.id')
            ->select('courses.description', 'courses.title', 'courses.id', 'courses.image',
                'courses.value', 'courses.is_disable', DB::raw('COUNT(user_courses.id) as qtdUser'))
            ->groupBy('courses.id')
            ->get();

        return $this->withoutEncryptData($courses);
    }

    protected function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'value' => 'required',
            'shop_news_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shopNews = Course::find($request['shop_news_id']);
        $shopNews->title = $request['title'];
        $shopNews->description = $request['description'];
        $shopNews->value = $request['value'];
        $shopNews->save();

        $response = [
            'message' => "Curso atualizado com sucessp!"
        ];

        return $this->withoutEncryptData($response);

    }

    protected function enable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $course = Course::where(['id' => $request['course_id']])->first();
        $course->is_disable = !$course->is_disable;
        $course->save();

        $response = [
            'message' => "status modificado com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $filename = $this->storeFile($request['pdf']);
        $image = $this->storeImage($request['image']);

        Course::create(['title' => $request['title'],
            'description' => $request['description']
            , 'value' => $request['value'],
            'pdf' => $filename, 'image' => $image]);

        $response = [
            'message' => "Curso criado com sucesso!"
        ];

        return $this->withoutEncryptData($response);

    }

    private function storeFile($file)
    {
        $filename = $file->store('files', 'public');
        $filename = str_replace('/files', '', $filename);
        return $filename;
    }

    private function storeImage($image)
    {
        $filename = $image->store('images', 'public');
        $filename = str_replace('/images', '', $filename);
        return $filename;
    }
}