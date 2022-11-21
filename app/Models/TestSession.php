<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = ['test_id','name','notice','start_at','end_at','description'];

    public $incrementing = false;
    protected $keyType = 'string';

    public static function boot() 
    {
        parent::boot();

        static::creating(function($model) {
            do {
                $uuid = Str::uuid()->toString();
            } while (DB::table('tests')->where('id',$uuid)->exists());

            $model->id = $uuid;
        }); 
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'test_session_questions');
    }
}
