<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestParticipantsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $participants = [];

        foreach($this->participants as $participant)  {
            array_push($participants, [
                'id' => $participant->id,
                'name' => $participant->participant->fullname,
                'assessor' => [
                    'id' => $participant->assesor->id,
                    'name' => $participant->assesor->name
                ],
                'verbatimer' => [
                    'id' => $participant->verbatimer->id,
                    'name' => $participant->verbatimer->name
                ]
            ]);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'participants' => $participants
        ];
    }
}
