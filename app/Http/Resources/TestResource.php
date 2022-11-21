<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sessions = [];

        foreach($this->sessions as $session) {
            array_push($sessions, [
                'id' => $session->id,
                'name' => $session->name,
                'notice' => $session->notice,
                'start_at' => $session->start_at,
                'end_at' => $session->end_at,
                'description' => $session->description,
                'num_of_question' => $session->questions->count()
            ]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'test_date' => Carbon::create($this->test_date)->format('Y-m-d'),
            'test_start_at' => $this->test_start_at,
            'test_end_at' => $this->test_end_at,
            'session' => $sessions
        ];
    }
}
