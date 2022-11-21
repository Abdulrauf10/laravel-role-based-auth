<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionBankResource;
use App\Http\Resources\QuestionBanksResource;
use App\Http\Resources\QuestionsResource;
use App\Models\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\map;

class QuestionBankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->q) {
            $questionBanks = QuestionBank::where('name','like','%'.$request->q.'%')->paginate($request->offset ?? 20);
        } else {
            $questionBanks = QuestionBank::orderBy('id','desc')->paginate($request->offset ?? 20);
        }
        
        if($questionBanks->count() < 1) {
            return response([
                'http_code' => 404,
                'success' => true,
                'message' => 'Question Data is not found',
            ],404);
        }

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, Question Data found',
            'data' => new QuestionBanksResource($questionBanks)
        ], 200);
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
            QuestionBank::create($request->all()+['created_by' => $user->id]);
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
        $questionBank = QuestionBank::find($id);
        
        if(!$questionBank) {
            return response([
                'http_code' => 404,
                'success' => false,
                'message' => 'Data not found :('
            ],404);
        }

        return response([
            'http_code' => 200,
            'success' => true,
            'data' => new QuestionBankResource($questionBank)
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(QuestionBank $questionBank)
    {
        
    }

    public function questions($id, Request $request)
    {
        $questionBank = QuestionBank::find($id);

        $questions = $questionBank->questions()->paginate($request->offset ?? 30);
        return response([
            'http_code' => 200,
            'success' => true,
            'data' => new QuestionsResource($questions)
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuestionBank $questionBank)
    {
        if(!$questionBank) {
            return response(['http_code' => 404, 'success' => 'false', 'message' => 'Question bank not found!'],404);
        }
        
        $request->validate([
            'name' => 'string|required|min:3|max:255'
        ]);

        DB::transaction(function() use($request, $questionBank) {
            $questionBank->update($request->all());
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
    public function destroy(QuestionBank $questionBank)
    {
        if(!$questionBank) {
            return response(['http_code' => 404, 'success' => 'false', 'message' => 'Question bank not found!'],404);
        }
        DB::transaction(function() use($questionBank) {
            // DB::table('question_bank_question')->where('question_bank_id', $questionBank->id)->delete();

            $questionBank->questions()->detach();
            $questionBank->delete();
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeeay, Data deleted!'
        ],200);
    }
}
