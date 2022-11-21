<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestParticipant extends Model
{
    use HasFactory;

    protected $fillable = ['test_id','code','participant_id','assesor_id','verbatimer_id'];

    public $incrementing = false;
    protected $keyType = 'string';

    public static function boot() 
    {
        parent::boot();

        static::creating(function($model) {
            do {
                $pool = '123456789ABCDEFGHJKMNPQRSTUVWXYZ';
                $code = substr(str_shuffle(str_repeat($pool, 5)), 0, 6);
            } while (DB::table('test_participants')->where('code',$code)->exists());
            $model->id = Str::uuid();
            $model->code = $code;
        }); 
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function assesor()
    {
        return $this->belongsTo(User::class, 'assesor_id');
    }

    public function verbatimer()
    {
        return $this->belongsTo(User::class, 'verbatimer_id');
    }
}
