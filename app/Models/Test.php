<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Test extends Model
{
    use HasFactory;

    protected $fillable = ['name','test_date','test_start_at','test_end_at','description'];

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

    public function sessions()
    {
        return $this->hasMany(TestSession::class);
    }

    public function participants()
    {
        return $this->hasMany(TestParticipant::class);
    }
}
