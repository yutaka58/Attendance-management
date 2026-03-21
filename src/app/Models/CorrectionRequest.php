<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    public $fillable = [
        'attendance_id',
        'user_id',
        'start_time',
        'end_time',
        'rest_start',
        'rest_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
