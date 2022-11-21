<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuestionsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $questions = [];
        foreach($this->collection as $item) {
            
            if($item->questionBank) {
                $question_banks = $this->getQuestionBank($item);
            }

            if($item->categories) {
                $categories = $this->getCategories($item);
            }

            if($item->answers) {
                $answer = $this->getAnswer($item);
            }
            
            if($item->key) {
                $key = [
                    'id' => $item->getKey()->id ?? null,
                    'name' => $item->getKey()->answer ?? null
                ];
            }

            array_push($questions, [
                'id' => $item->id,
                'question' => $item->question ?? '',
                'answer' => $answer ?? null,
                'type' => $item->type,
                'key' => $key ?? null,
                'question_banks' => $question_banks ?? null,
                'categories' => $categories ?? null
            ]);
        }

        return [
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'total_page' => $this->lastPage(),
            'total' => $this->total(),
            'data' => $questions
        ];
    }

    private function getQuestionBank($item)
    {
        $question_banks = [];
        foreach($item->questionBank as $itm) {
            array_push($question_banks, [
                'id' => $itm->id,
                'name' => $itm->name
            ]);
        }

        return $question_banks;
    }

    private function getCategories($item) 
    {
        $categories = [];
        foreach($item->categories as $itm) {
            array_push($categories, [
                'id' => $itm->id,
                'name' => $itm->name
            ]);
        }

        return $categories;
    }

    private function getAnswer($item) 
    {
        $answer = [];
        foreach($item->answers as $itm) {
            array_push($answer, [
                'id' => $itm->id,
                'name' => $itm->answer
            ]);
        }
        return $answer;
    }
}
