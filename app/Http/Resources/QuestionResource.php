<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->questionBank) {
            $question_banks = $this->getQuestionBank($this);
        }

        if($this->categories) {
            $categories = $this->getCategories($this);
        }

        if($this->answers) {
            $answer = $this->getAnswer($this);
        }
        
        if($this->key) {
            $key = [
                'id' => $this->getKey()->id ?? null,
                'name' => $this->getKey()->answer ?? null
            ];
        }

        if($this->attachment) {
            $attachment = Storage::disk('s3')->temporaryUrl($this->attachment_key, Carbon::now()->addMinutes(60));
        } else {
            $attachment = null;
        }

        return [
            'id' => $this->id,
            'question' => $this->question ?? '',
            'answer' => $answer ?? null,
            'type' => $this->type,
            'key' => $key ?? null,
            'question_banks' => $question_banks ?? null,
            'categories' => $categories ?? null,
            'attachment_type' => $this->attachment_type,
            'attachment' => $attachment
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
