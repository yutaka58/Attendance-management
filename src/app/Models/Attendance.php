<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_action_id',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function correctionRequest()
    {
        return $this->hasMany(correctionRequest::class);
    }
}
