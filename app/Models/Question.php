<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['question','type','key','created_by','attachment','attachment_type','attachment_key'];

    public function questionBank()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_bank_questions');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_has_questions');
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class,'question_id');
    }

    public function getKey()
    {
        return QuestionAnswer::find($this->key) ?? null;
    }

    public function tests()
    {
        return $this->belongsToMany(TestSession::class, 'test_session_question');
    }
}
