<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\QuestionsResource;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $questions = new Question();

        if($request->q ?? null) {
            $questions = $questions->where('name','like','%'.$request->q.'%')->paginate($request->offset ?? 30);
        } 

        if($request->categories ?? null) {
            $questions = $questions->whereHas('categories', function($query) use($request) {
                $query->whereIn('id',$request->categories);
            });
        }

        if($request->questionBank ?? null) {
            $questions = $questions->whereHas('questionBank', function($query) use($request) {
                $query->whereIn('id',$request->questionBank);
            });
        }

        $questions = $questions->orderBy('id','desc')->paginate($request->offset ?? 30);

        if($questions->count() < 1) {
            return response([
                'http_code' => 200,
                'success' => true,
                'message' => 'Question data not found',
            ],200);
        }

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, question data found!',
            'data' => new QuestionsResource($questions)
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
            'question' => 'required|string|min:3|max:255',
            'type' => 'required|in:multiple,essay,single, description',
            'answer' => 'array|nullable|sometimes',
            'answer.*' => 'string|nullable|sometimes',
            'key' => 'nullable|sometimes|string',
            'question_banks' => 'array|nullable|sometimes',
            'question_banks.*' => 'numeric|nullable|sometimes',
            'categories' => 'array|nullable|sometimes',
            'categories.*' => 'numeric|nullable|sometimes',
            'attachment' => 'sometimes|nullable|mimes:jpg,jpeg,png,heic,pdf,docx,doc,xls,xlsx',
            'attachment_type' => 'sometimes|nullable|string|in:image,file,pdf,downloadable'
        ]);

        DB::transaction(function () use($request) {
            $question = Question::create([
                'question' => $request->question,
                'type' => $request->type,
                'attachment_type' => $request->attachment_type ?? null,
                'created_by' => Auth::user()->id
            ]);

            if($request->answer) {
                // $question->answers()->associate($request->answer);
                foreach($request->answer as $answer) {
                    if($answer) {
                        QuestionAnswer::create([
                            'question_id' => $question->id,
                            'answer' => $answer
                        ]);
                    }
                }
            }

            if($request->question_banks){
                $question->questionBank()->attach($request->question_banks);
            }

            if($request->categories){
                $question->categories()->attach($request->categories);
            }

            if($request->key) {
                $answer = QuestionAnswer::where('answer',$request->key)->where('question_id',$question->id)->first();

                if(!$answer) {
                    return response(['http_code' => 422,'success' => false, 'message' => 'answer key is not valid'],422);
                } 

                $question->key = $answer->id;
                $question->save();
            }

            if ($request->file('attachment')) {
                $filename = 'question-'.$question->id.'.'.$request->attachment->extension();

                $path = Storage::disk('s3')->putFileAs('question/'.$question->id, $request->attachment, $filename);
                $path = Storage::disk('s3')->url($path);

                $question->attachment = $path;
                $question->attachment_key = 'question/'.$question->id.'/'.$filename;
                $question->save();
            }
            
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, data saved'
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question = Question::find($id);

        if(!$question) {
            return response([
                'http_code' => 404,
                'success' => false,
                'message' => 'Data not found :('
            ],404);
        }

        return response([
            'http_code' => 200,
            'success' => true,
            'data' => new QuestionResource($question)
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question' => 'required|string|min:3|max:255',
            'type' => 'required|in:multiple,essay,single,description',
            'answer' => 'array|nullable|sometimes',
            'answer.*' => 'string|nullable|sometimes',
            'key' => 'string',
            'question_banks' => 'array|nullable|sometimes',
            'question_banks.*' => 'numeric|nullable|sometimes',
            'categories' => 'array|nullable|sometimes',
            'categories.*' => 'numeric|nullable|sometimes',
            'attachment' => 'sometimes|nullable|mimes:jpg,jpeg,png,heic,pdf,docx,doc,xls,xlsx',
            'attachment_type' => 'sometimes|nullable|string|in:image,file,pdf,downloadable'
        ]);

        DB::transaction(function () use($request, $question) {
            $question->update([
                'question' => $request->question,
                'type' => $request->type,
                'created_by' => Auth::user()->id,
                'attachment_type' => $request->attachment_type ?? null,
            ]);

            if($request->answer) {
                QuestionAnswer::where('question_id',$question->id)->delete();
                // $question->answers()->associate($request->answer);
                foreach($request->answer as $answer) {
                    if($answer) {
                        QuestionAnswer::create([
                            'question_id' => $question->id,
                            'answer' => $answer
                        ]);
                    }
                }
            }

            if($request->key) {
                $answer = QuestionAnswer::where('answer',$request->key)->where('question_id',$question->id)->first();

                if(!$answer) {
                    return response(['http_code' => 422,'success' => false, 'message' => 'answer key is not valid'],422);
                } 
                $question->key = $answer->id;
                $question->save();

            }

            if($request->categories){
                $question->questionBank()->sync($request->question_banks);
            }

            if($request->question_banks){
                $question->categories()->sync($request->categories);
            }

            if ($request->file('attachment')) {
                $filename = 'question-'.$question->id.'.'.$request->attachment->extension();

                $path = Storage::disk('s3')->putFileAs('question/'.$question->id, $request->attachment, $filename);
                $path = Storage::disk('s3')->url($path);

                $question->attachment = $path;
                $question->attachment_key = 'question/'.$question->id.'/'.$filename;
                $question->save();
            }
            
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, data updated'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        DB::transaction(function() use($question) {
            QuestionAnswer::where('question_id',$question->id)->delete();
            $question->questionBank()->detach();
            $question->delete();
        });

        return response([
            'http_code' => 200,
            'success' => true,
            'message' => 'Yeey, data deteleted'
        ],200);

    }
}
