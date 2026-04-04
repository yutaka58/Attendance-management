<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_action_id',
        'start_time',
        'end_time',
        'rest_start',
        'rest_end',
        'remarks',
        'total_rest_time',
        'work_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function correctionRequest()
    {
        return $this->hasMany(correctionRequest::class);
    }

    /**
     * 特定の打刻グループ（その日の全打刻）から休憩合計（分）を計算する
     */
    public static function getRestMinutes($group)
    {
        $total_rest_minutes = 0;
        $clockIn = $group->where('work_action_id', 1)->first();

        // パターンA: JSON形式（承認済みデータ）
        if ($clockIn && !empty($clockIn->rest_start) && str_contains($clockIn->rest_start, '[')) {
            $starts = json_decode($clockIn->rest_start, true);
            $ends = json_decode($clockIn->rest_end, true);
            if (is_array($starts) && is_array($ends)) {
                foreach ($starts as $i => $s) {
                    if (!empty($s) && !empty($ends[$i])) {
                        $total_rest_minutes += Carbon::parse($s)->diffInMinutes(Carbon::parse($ends[$i]));
                    }
                }
            }
        }
        // パターンB: 通常形式（別行データ）
        else {
            $restStarts = $group->where('work_action_id', 3)->sortBy('created_at');
            foreach ($restStarts as $start) {
                $end = $group->where('work_action_id', 4)
                    ->where('created_at', '>', $start->created_at)
                    ->sortBy('created_at')->first();
                if ($end) {
                    $sTime = !empty($start->rest_start) ? Carbon::parse($start->rest_start) : $start->created_at;
                    $eTime = !empty($end->rest_end) ? Carbon::parse($end->rest_end) : $end->created_at;
                    $total_rest_minutes += $sTime->diffInMinutes($eTime);
                }
            }
        }
        return $total_rest_minutes;
    }

    /**
     * 特定の打刻グループから実労働時間（H:i形式）を計算する
     */
    public static function getWorkTimeDisplay($group)
    {
        $clockIn = $group->where('work_action_id', 1)->first();
        $clockOut = $group->where('work_action_id', 2)->first();

        $s_val = $clockIn->start_time ?? ($clockIn ? $clockIn->created_at->format('H:i') : null);
        $e_val = $clockIn->end_time ?? ($clockOut->end_time ?? ($clockOut ? $clockOut->created_at->format('H:i') : null));

        if (!$s_val || !$e_val) return '';

        $totalMinutes = Carbon::parse($s_val)->diffInMinutes(Carbon::parse($e_val));
        $restMinutes = self::getRestMinutes($group);
        $netMinutes = max(0, $totalMinutes - $restMinutes);

        return sprintf('%02d:%02d', floor($netMinutes / 60), $netMinutes % 60);
    }
}
