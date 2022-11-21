<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuestionBanksResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $questionBanks = [];

        foreach($this->collection as $item) {
            array_push($questionBanks, [
                'id' => $item->id,
                'name' => $item->name,
                'num_of_question' => $item->questions->count()
            ]);
        }

        return [
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'total_page' => $this->lastPage(),
            'total' => $this->total(),
            'data' => $questionBanks,
        ];
    }
}
