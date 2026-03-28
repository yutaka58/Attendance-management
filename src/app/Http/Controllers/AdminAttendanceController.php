<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attendance;
use App\Models\User;
use App\Models\CorrectionRequest;;

use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
        public function showAttendanceList(Request $request)
    {
        // 1. パラメータがなければ今日、あればその日を取得
        $dateParam = $request->query('date', Carbon::now()->format('Y-m-d'));
        $currentDay = Carbon::parse($dateParam);

        $prevDay = $currentDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDay->copy()->addDay()->format('Y-m-d');

        $startOfDay = $currentDay->copy()->startOfDay();
        $endOfDay = $currentDay->copy()->endOfDay();

        $attendances = Attendance::whereBetween('created_at', [$startOfDay, $endOfDay])->orderBy('created_at', 'asc')->get()->groupBy('user_id');

        return view('admin_attendance_list', compact('currentDay', 'prevDay', 'nextDay', 'attendances'));
    }

    public function adminDetail($id)
    {
        $targetDate = Carbon::parse($id);
        $currentDay = $targetDate;

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();
        $userId = request('user_id');
        $user = \App\Models\User::find($userId);

        // 出勤打刻を取得
        $attendance = Attendance::where('user_id', $userId)->where('work_action_id', 1)->whereBetween('created_at', [$startOfDay, $endOfDay])->first();

        // 退勤打刻を取得
        $end_attendance = Attendance::where('user_id', $userId)->where('work_action_id', 2)->whereBetween('created_at', [$startOfDay, $endOfDay])->first();

        // 休憩入・休憩戻の打刻時間を取得
        $rests_start = Attendance::where('user_id', $userId)->where('work_action_id', 3)->whereBetween('created_at', [$startOfDay, $endOfDay])->orderBy('created_at', 'asc')->get();
        $rests_end = Attendance::where('user_id', $userId)->where('work_action_id', 4)->whereBetween('created_at', [$startOfDay, $endOfDay])->orderBy('created_at', 'asc')->get();
        // 休憩のペアを作成

        $correction = null;
        if ($attendance) {
            $correction = CorrectionRequest::where('user_id', $userId)->where('attendance_id', $attendance->id)->latest()->first();
        }

        $rests = [];
        if($correction) {
            // 修正の申請があった場合、申請データを表示用に更新
            $c_starts = json_decode($correction->rest_start, true) ?? [];
            $c_ends = json_decode($correction->rest_end, true) ?? [];
                foreach($c_starts as $index => $val) {
                    if(!empty($val)) {
                        $rests[] = ['start' => $val, 'end' => $c_ends[$index] ?? ''];
                    }
                }
        } else {
            // 申請がなかった場合、元のデータを表示
            foreach($rests_start as $index => $start) {
                $rests[] = [
                    'start' => $start->created_at->format('H:i'),
                    'end' => isset($rests_end[$index]) ? $rests_end[$index]->created_at->format('H:i'): null
                ];
            }
        }

        // 表示用の変数
        $year = $targetDate->format('Y');
        $month = $targetDate->format('n');
        $day = $targetDate->format('j');

        // ★ 常に空の休憩枠を1つ追加する
        $rests[] = ['start' => '', 'end' => ''];

        // 申請があればそれを優先、なければ元の打刻。どちらもなければ null
        $start_time = $correction ? $correction->start_time : ($attendance ? $attendance->created_at->format('H:i') : '');
        $end_time = $correction ? $correction->end_time : ($end_attendance ? $end_attendance->created_at->format('H:i') : '');

        return view('admin_attendance_detail', compact('attendance', 'year', 'month', 'day', 'start_time', 'end_time', 'rests', 'correction', 'currentDay', 'userId', 'user'));
    }

    public function staffList()
    {
        return view('admin_staff_list');
    }

    public function staffDetail()
    {
        return view('admin_attendance_staff');
    }
}
