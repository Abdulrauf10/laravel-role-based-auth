<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = ['name','created_by'];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_bank_questions');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
