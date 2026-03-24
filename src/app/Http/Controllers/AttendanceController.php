<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceDetailRequest;

use App\Models\WorkStatus;
use App\Models\WorkAction;
use App\Models\Attendance;
use App\Models\User;
use App\Models\CorrectionRequest;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $user = auth()->user();
        // ユーザーに紐づく現在のステータスを取得

        // 今日の打刻があるか確認
        $alreadyCheckedIn = Attendance::where('user_id', auth()->id())->whereDate('created_at', Carbon::today())->exists();

        // 今日の打刻がまだだが、「勤務外」などのステータスとなっていた場合リセットする
        // 初期ログイン時の「work_status」が null の場合も考慮
        if (!$alreadyCheckedIn && (!$user->work_status || $user->work_status->name !== '勤務外')) {
            $initialStatus = WorkStatus::where('name', '勤務外')->first();
            $user->update(['work_status_id' => $initialStatus->id]);

            // リレーションを最新に更新
            $user->load('work_status');
        }

        $work_status = $user->work_status;

        // 現在のステータスに応じて、表示するボタンを固定
        $display_actions = [];

        if (!$work_status || $work_status->name ==='勤務外') {
            $display_actions = WorkAction::where('name', '出勤')->get();
        } elseif ($work_status->name === '出勤中') {
            $display_actions = WorkAction::whereIn('name', ['退勤', '休憩入'])->get();
        } elseif ($work_status->name === '休憩中') {
            $display_actions = WorkAction::where('name', '休憩戻')->get();
        } elseif ($work_status->name === '退勤済') {
            $display_actions = WorkAction::where('name', 'お疲れ様でした。')->get();
        }

        // 本日に「出勤」状態のユーザーであれば、表示させない
        $alreadyCheckedIn = Attendance::where('user_id', auth()->id())->whereDate('created_at', Carbon::today())->exists();
        $work_actions = WorkAction::all();
        if ($alreadyCheckedIn) {
            $work_actions = $work_actions->where('name', '!=', '出勤');
        }

        // 年月日、時間のフォーマット指定
        $dt = Carbon::today()->isoFormat('YYYY年M月D日(ddd)');
        $time = Carbon::now()->format('H:i');

        return view('/attendance', compact('work_status', 'dt', 'time', 'work_actions', 'display_actions', 'alreadyCheckedIn'));
    }

    public function storeAttendance(Request $request)
    {
        // 本日に「出勤」状態のユーザーであれば、表示させない
        $alreadyCheckedIn = Attendance::where('user_id', auth()->id())->whereDate('created_at', Carbon::today())->exists();
        $work_actions = WorkAction::all();
        if ($alreadyCheckedIn) {
            $work_actions = $work_actions->where('name', '!=', '出勤');
        }

        $user = auth()->user();
        $action = WorkAction::find($request->action_id);

        // 打刻ログを保存
        Attendance::create([
            'user_name'=> $user->name,
            'user_id' => $user->id,
            'work_action_id' => $action->id,
        ]);

        // 現在のステータスに合わせて表示項目変化
        if ($action->name === '出勤') {
            $status = WorkStatus::where('name', '出勤中') ->first();
            $user->update(['work_status_id' => $status->id]);
        } elseif ($action->name === '休憩入') {
            $status = WorkStatus::where('name', '休憩中')->first();
            $user->update(['work_status_id' => $status->id]);
        } elseif ($action->name === '休憩戻') {
            $status = WorkStatus::where('name', '出勤中')->first();
            $user->update(['work_status_id' => $status->id]);
        } elseif ($action->name === '退勤') {
            $status = WorkStatus::where('name', '退勤済')->first();
            $user->update(['work_status_id' => $status->id]);
        }

        return redirect()->back();
    }

    public function attendanceList(Request $request)
    {
        // パラーメータがなければ今月を取得
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $user = auth()->user();
        // ユーザーの打刻データを取得し、日付ごとにグループ化する

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $tempDay = $startOfMonth->copy();

        $allDays = [];
        $timeDay = $startOfMonth->copy();
        while($tempDay->lte($endOfMonth)) {
            $allDays[$tempDay->format('Y-m-d')] = collect();
            $tempDay->addDay();
        }

        $dbAttendances = Attendance::where('user_id', $user->id)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->orderBy('created_at', 'asc')->get()->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        });

        $attendances = collect($allDays)->merge($dbAttendances);

        return view('attendance_list', compact('monthParam', 'currentMonth', 'prevMonth', 'nextMonth', 'attendances', 'tempDay'));
    }

    public function attendanceDetail($date)
    {
        $targetDate = Carbon::parse($date);

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();
        $userId = auth()->id();

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

        return view('attendance_detail', compact('attendance', 'year', 'month', 'day', 'start_time', 'end_time', 'rests', 'correction'));
    }

    public function timeCorrection(AttendanceDetailRequest $request)
    {
        $userId = auth()->id();

        // 空の休憩時間を除外して保存
        $rest_starts = array_filter($request->rest_start);
        $rest_ends = array_filter($request->rest_end);

        CorrectionRequest::create([
            'attendance_id' => $request->attendance_id,
            'user_id' => $userId,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,

            'rest_start' => is_array($request->rest_start) ? json_encode($request->rest_start) : $request->rest_start,
            'rest_end' => is_array($request->rest_end) ? json_encode($request->rest_end) : $request->rest_end,

            'status' => 0,
            'remarks' => $request->remarks_column,
        ]);

    return redirect()->back()->with('message', '*承認待ちのため修正はできません。');
    }
}
