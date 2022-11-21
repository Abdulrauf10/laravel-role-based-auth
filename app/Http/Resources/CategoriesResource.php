<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoriesResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $categories = [];

        foreach($this->collection as $item) {
            array_push($categories, [
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
            'data' => $categories
        ];
    }
}
