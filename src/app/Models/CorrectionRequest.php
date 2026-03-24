<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    // ステータスの定義
    const STATUS_PENDING = 0;
    const STATUS_APPROVE = 1;

    public $fillable = [
        'attendance_id',
        'user_id',
        'start_time',
        'end_time',
        'rest_start',
        'rest_end',
        'remarks',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
