<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestParticipantsResource;
use App\Http\Resources\TestResource;
use App\Http\Resources\TestSessionsResource;
use App\Http\Resources\TestsResource;
use App\Imports\ParticipantImport;
use App\Models\Participant;
use App\Models\Test;
use App\Models\TestParticipant;
use App\Models\TestSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{
    public function index(Request $request)
    {
        
        $tests = new Test();

        if($request->q) {
            $tests = $tests->where('name','like','%'.$request->q.'%');
        }

        if($request->test_date) {
            
        }

        $tests = $tests->orderBy('test_date','DESC')->orderBy('id','DESC')->paginate($request->offset ?? 20);

        return response([
            'http_code' => 200,
            'message' => 'Yeey, test data found :D',
            'data' => new TestsResource($tests)
        ],200);
    }

    public function show(Test $test)
    {
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found :D',
            'data' => new TestResource($test)
        ],200);
    }

    public function participants(Test $test)
    {
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data participant found :D',
            'data' => new TestParticipantsResource($test)
        ],200);
    }

    public function sessions(TestSession $testsession)
    {
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data participant found :D',
            'data' => new TestSessionsResource($testsession)
        ],200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|min:3|max:55',
            'test_date' => 'sometimes|nullable|date', 
            'test_start_at' => 'sometimes|nullable|date_format:H:i',
            'test_end_at'   => 'sometimes|nullable|date_format:H:i',
            'description'   => 'sometimes|nullable'
        ]);

        $test = DB::transaction(function () use($request) {
            return Test::create($request->all());
        });

        return response(['http_code' => 200,'success' => true, 'message' => 'Test data has been saved', 'data' => $test],200);
    }

    public function storeSession(Test $test, Request $request)
    {
        $request->validate([
            'name'      => 'required|string',
            'notice'    => 'sometimes|nullable|string',
            'start_at'  => 'sometimes|nullable|date_format:H:i',
            'end_at'    => 'sometimes|nullable|date_format:H:i|after:start_at',
            'notice'    => 'sometimes|nullable',
            'description' => 'sometimes|nullable|string',
            'questions' => 'sometimes|nullable|array',
            'questions.*' => 'required|numeric|exists:questions,id'
        ]);

        if($test->sessions && $request->start_at) {
            $lastSession = $test->sessions->last();
            if($lastSession) {
                $lastEndSession = Carbon::create($lastSession->end_at)->format('H:i');
            
                if(Carbon::create($request->start_at)->format('H:i') < $lastEndSession) {
                    return response(['http_code' => 422, 'message' => 'start-at session must be grather or equal to '.$lastEndSession],422);
                }
            }
        }

        if($test->test_start_at && $test->test_end_at) {
            $testStartAt = Carbon::create($test->test_start_at)->format('H:i');
            $testEndAt = Carbon::create($test->test_end_at)->format('H:i');
            
            if ( Carbon::create($request->start_at)->format('H:i') < $testStartAt) {
                return response(['http_code' => 422, 'message' => 'start-at session must be grather or equal to '.$testStartAt], 422);
            }  
            
            if(Carbon::create($request->end_at)->format('H:i') > $testEndAt ) {
                return response(['http_code' => 422, 'message' => 'end-at session must be less or equal to '.$testEndAt], 422);
            }
        }

        $session = DB::transaction(function () use($request, $test) {
            $testSession = TestSession::create([
                'test_id'   => $test->id,
                'name'      => $request->name,
                'notice'    => $request->notice,
                'start_at'  => $request->start_at,
                'end_at'    => $request->end_at,
                'description' => $request->description
            ]);

            // store test session question
            if($request->questions) {
                $testSession->questions()->attach($request->questions);
            }

            return $testSession;
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, Test session has been saved!', 'data' => $session],200);
    }

    public function parseParticipant(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');
        $parseData = Excel::toArray(new ParticipantImport(), $file);
        
        $participants = [];
        for($i = 0; $i < count($parseData[0]); $i++) {
            if($i == 0) {
                if($parseData[0][0][0] != 'name' && $parseData[0][0][1] != 'email') {
                    return response([
                        'http_code' => 422,
                        'message' => 'Invalid Excel file!'
                    ], 422);
                }
            } else {
                array_push($participants, [
                    'name' => $parseData[0][$i][0],
                    'email' => $parseData[0][$i][1],
                ]);
            }
        }

        return response([
            'http_code' => 200,
            'message' => 'Yeeay, Excel file has been parsed to participant data raw',
            'data' => $participants
        ],200);
    }

    public function storeParticipant(Test $test, Request $request)
    {
        $request->validate([
            'participant_name'      => 'required|array|min:1',
            'participant_name.*'    => 'required|string',
            'participant_email'     => 'required|array|min:1',
            'participant_email.*'   => 'required|email',
            'verbatimers'           => 'nullable|sometimes|array',
            'verbatimers.*'         => 'nullable|sometimes|numeric|exists:users,id',
            'assessors'             => 'nullable|sometimes|array',
            'assessors.*'           => 'nullable|sometimes|numeric|exists:users,id'
        ]);

        DB::transaction(function() use($request, $test) {
            for($i = 0; $i < count($request->participant_name); $i++) {

                // check if participant exists in database?
                $participant = Participant::updateOrCreate(['email' => $request->participant_email[$i]], [
                    'fullname'  => $request->participant_name[$i],
                    'email'     => $request->participant_email[$i]
                ]);

                // check if participant has been registered to the test
                TestParticipant::updateOrCreate(
                    ['test_id' => $test->id, 'participant_id' => $participant->id],
                    [
                        'test_id'           => $test->id,
                        'participant_id'    => $participant->id,
                        'assesor_id'        => $request->assessors[$i],
                        'verbatimer_id'     => $request->verbatimers[$i]                    
                    ]
                );

            }
        });

        return response(['http_code' => 200, 'message' => 'yeeah! participant data has been saved :D'],200);
    }

    public function update(Test $test, Request $request)
    {
        $request->validate([
            'name'      => 'required|string|min:3|max:55',
            'test_date' => 'sometimes|nullable|date', 
            'test_start_at' => 'sometimes|nullable|date_format:H:i',
            'test_end_at'   => 'sometimes|nullable|date_format:H:i',
            'description'   => 'sometimes|nullable'
        ]);

        $newTest = DB::transaction(function () use($request, $test) {
            return $test->update($request->all());
        });

        return response(['http_code' => 200,'success' => true, 'message' => 'Test data has been saved', 'data' => $newTest],200);
    }

    public function updateSession(TestSession $testsession, Request $request)
    {
        $request->validate([
            'name'      => 'required|string',
            'notice'    => 'sometimes|nullable|string',
            'start_at'  => 'sometimes|nullable|date_format:H:i',
            'end_at'    => 'sometimes|nullable|date_format:H:i|after:start_at',
            'notice'    => 'sometimes|nullable',
            'description' => 'sometimes|nullable|string',
            'questions' => 'sometimes|nullable|array',
            'questions.*' => 'required|numeric|exists:questions,id'
        ]);

        // if($testsession->sessions && $request->start_at) {
        //     $lastSession = $testsession->sessions->last();
        //     $lastEndSession = Carbon::create($lastSession->end_at)->format('H:i');
            
        //     if(Carbon::create($request->start_at)->format('H:i') < $lastEndSession) {
        //         return response(['http_code' => 422, 'message' => 'start-at session must be grather or equal to '.$lastEndSession],422);
        //     }
        // }

        $startAt = Carbon::create($request->start_at)->format('H:i');
        $endAt = Carbon::create($request->end_at)->format('H:i');

        if($testsession->test_start_at && $testsession->test_end_at) {
            $testStartAt = Carbon::create($testsession->test_start_at)->format('H:i');
            $testEndAt = Carbon::create($testsession->test_end_at)->format('H:i');
            
            if ($startAt  < $testStartAt) {
                return response(['http_code' => 422, 'message' => 'start-at session must be grather or equal to '.$testStartAt], 422);
            }  
            
            if ($endAt > $testEndAt ) {
                return response(['http_code' => 422, 'message' => 'end-at session must be less or equal to '.$testEndAt], 422);
            }

        }

        // checking redundance time session
        $otherSession = TestSession::where('test_id', $testsession->test_id)
                                        ->whereNotIn('id',[$testsession->id])
                                        ->get();
        // dd($otherSession);
        $exists = false;
        foreach($otherSession as $session) {
            if ($startAt >= Carbon::create($session->start_at)->format('H:i') && $startAt < Carbon::create($session->end_at)->format('H:i')) {
                $exists = true;
                break;
            }

            if ($endAt > Carbon::create($session->start_at)->format('H:i') && $endAt < Carbon::create($session->end_at)->format('H:i')) {
                $exists = true;
                break;
            }
        }

        if($exists) {
            return response(['http_code' => 422, 'message' => 'time start and end is crash with other session, kindly check your start-at and end-at :D'], 422);
        }
        

        $session = DB::transaction(function () use($request, $testsession) {
            $newTestSession = $testsession->update([
                                'test_id'   => $testsession->id,
                                'name'      => $request->name,
                                'notice'    => $request->notice,
                                'start_at'  => $request->start_at,
                                'end_at'    => $request->end_at,
                                'description' => $request->description
                            ]);

            // store test session question
            if($request->questions) {
                $testsession->questions()->attach($request->questions);
            }

            return $newTestSession;
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, Test session has been updated!'],200);
    }

    public function destroy(Test $test)
    {
        DB::transaction(function() use($test) {
            TestSession::where('test_id',$test->id)->delete();
            TestParticipant::where('test_id',$test->id)->delete();
            $test->delete();
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, Test has been deleted!'],200);
    }

    public function deleteSession(TestSession $testsession)
    {
        DB::transaction(function() use($testsession) {
            $testsession->delete();
        });

        return response(['http_code' => 200, 'message' => 'Yeey, Test session has been deleted!'],200);
    }

    public function updateParticipant(TestParticipant $testParticipant, Request $request)
    {
        $request->validate([
            'assesor_id' => 'sometimes|nullable|exists:users,id',
            'verbatimer_id' => 'sometimes|nullable|exists:users,id',
        ]);

        DB::transaction(function() use($request, $testParticipant) {
            $testParticipant->update($request->all());
        });

        return response(['http_code' => 200, 'message' => 'Yeeay, Test participant has been updated :D'], 200);
    }

    public function deleteParticipant(TestParticipant $testParticipant)
    {
        DB::transaction(function() use($testParticipant) {
            $testParticipant->delete();
        });

        return response(['http_code' => 200, 'message' => 'Yeey, Test participant has been deleted!'], 200);
    }

}
