<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TestsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $tests = [];
        foreach($this->collection as $test) {
            array_push($tests, [
                'id' => $test->id,
                'name' => $test->name,
                'test_date' => Carbon::create($test->test_date)->format('Y-m-d'),
                'test_start_at' => $test->test_start_at,
                'test_end_at' => $test->test_end_at,
                'num_of_sessions' => $test->sessions->count(),
                'num_of_participants' => $test->participants->count()
            ]);
        }


        return $tests;
    }
}
