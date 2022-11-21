<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TestSessionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        
        $questions = [];

        foreach($this->questions as $question) {

            if($question->questionBank) {
                $question_banks = $this->getQuestionBank($question);
            }
    
            if($question->categories) {
                $categories = $this->getCategories($question);
            }
    
            if($question->answers) {
                $answer = $this->getAnswer($question);
            }
            
            if($question->key) {
                $key = [
                    'id' => $question->getKey()->id ?? null,
                    'name' => $question->getKey()->answer ?? null
                ];
            }
    
            if($question->attachment) {
                $attachment = Storage::disk('s3')->temporaryUrl($question->attachment_key, Carbon::now()->addMinutes(60));
            } else {
                $attachment = null;
            }

            array_push($questions, [
                'id' => $question->id,
                'question' => $question->question ?? '',
                'answer' => $answer ?? null,
                'type' => $question->type,
                'key' => $key ?? null,
                'question_banks' => $question_banks ?? null,
                'categories' => $categories ?? null,
                'attachment_type' => $question->attachment_type,
                'attachment' => $attachment
            ]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'questions' => $questions
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
