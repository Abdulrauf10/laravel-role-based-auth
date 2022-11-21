<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $participants = new Participant();
        
        if($request->name) {
            $participants = $participants->where('fullname','like','%'.$request->name.'%');
        }

        $participants = $participants->orderBy('id','desc')->paginate($request->offset ?? 20);
        
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found :D',
            'data' => $participants
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
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
            'fullname' => 'required|string|min:3',
            'email' => 'required|email|unique:participants,email',
            'phone' => 'sometimes|nullable',
            'birth_date' => 'sometimes|nullable|date',
            'city_id' => 'sometimes|nullable|numeric|exists:indonesia_cities,id',
            'education' => 'sometimes|nullable',
            'gender' => 'sometimes|nullable|in:male,female'
        ]);

        DB::transaction(function() use($request) {
            Participant::create($request->all());
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, participant data has been saved!'],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Participant $participant)
    {
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data participant found',
            'data' => $participant
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
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Participant $participant)
    {
        $request->validate([
            'fullname' => 'required|string|min:3',
            'email' => 'required|email|unique:participants,email,'.$participant->id,
            'phone' => 'sometimes|nullable',
            'birth_date' => 'sometimes|nullable|date',
            'city_id' => 'sometimes|nullable|numeric|exists:indonesia_cities,id',
            'education' => 'sometimes|nullable',
            'gender' => 'sometimes|nullable|in:male,female'
        ]);

        DB::transaction(function() use($request, $participant) {
            $participant->update($request->all());
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, participant data has been saved!'],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Participant $participant)
    {
        DB::transaction(function() use($participant) {
            $participant->delete();
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, participant data has been deleted!'],200);
    }
}
