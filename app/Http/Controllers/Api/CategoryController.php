<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\QuestionsResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->q) {
            $categories = Category::where('name','like','%'.$request->q.'%')->paginate($request->offset ?? 30);
        } else {
            $categories = Category::orderBy('id','desc')->paginate($request->offset ?? 30);
        }
        
        if($categories->count() < 1) {
            return response([
                'http_code' => 404,
                'success' => true,
                'message' => 'Oops, categories data not found!'
            ],404);
        } 

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, categories data found',
            'data' => new CategoriesResource($categories)
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|required|min:3|max:255'
        ]);

        DB::transaction(function () use($request) {
            $user = Auth::user();
            Category::create($request->all()+['created_by' => $user->id]);
        });

        return response(['success' => true, 'message' => 'Yeey, data created!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);

        if(!$category) {
            return response([
                'http_code' => 404,
                'success' => false,
                'message' => 'Data not found'
            ],404);
        }

        return response([
            'http_code' => 200,
            'success' => true,
            'data' => new CategoryResource($category)
        ],200);
    }

    public function questions($id, Request $request)
    {
        $category = Category::find($id);

        $questions = $category->questions()->paginate($request->offset ?? 30);
        return response([
            'http_code' => 200,
            'success' => true,
            'data' => new QuestionsResource($questions)
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        if(!$category) {
            return response(['http_code' => 404, 'success' => 'false', 'message' => 'Question bank not found!'],404);
        }
        
        $request->validate([
            'name' => 'string|required|min:3|max:255'
        ]);

        DB::transaction(function() use($request, $category) {
            $category->update($request->all());
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, data updated!'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if(!$category) {
            return response(['http_code' => 404, 'success' => 'false', 'message' => 'Question bank not found!'],404);
        }
        DB::transaction(function() use($category) {
            // DB::table('question_bank_question')->where('question_bank_id', $questionBank->id)->delete();

            $category->questions()->detach();
            $category->delete();
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeeay, Data deleted!'
        ],200);
    }
}
