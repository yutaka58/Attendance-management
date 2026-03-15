<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\WorkStatus;
use App\Models\WorkAction;
use App\Models\Attendance;
use App\Models\User;

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

        $attendances = Attendance::where('user_id', $user->id)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->orderBy('created_at', 'asc')->get()->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        });

        return view('attendance_list', compact('monthParam', 'currentMonth', 'prevMonth', 'nextMonth', 'attendances'));
    }

    public function attendanceDetail()
    {
        // 出勤打刻時間を取得
        $attendance = Attendance::where('user_id', auth()->id())->where('work_action_id', 1)->orderBy('created_at', 'desc')->first();
        $year = $attendance ? $attendance->created_at->format('Y'): null;
        $month = $attendance ? $attendance->created_at->format('n'): null;
        $day = $attendance ? $attendance->created_at->format('j'): null;

        $start_time = $attendance ? $attendance->created_at->format('H:i'): null;

        // 退勤打刻時間を取得
        $end_attendance = Attendance::where('user_id', auth()->id())->where('work_action_id', 2)->orderBy('created_at', 'desc')->first();
        $end_time = $end_attendance ? $end_attendance->created_at->format('H:i'): null;


        // 休憩入(id:3)と休憩戻(id:4)をすべて取得
        $rests_start = Attendance::where('user_id', auth()->id())->where('work_action_id', 3)->orderBy('created_at', 'asc')->get();
        $rests_end = Attendance::where('user_id', auth()->id())->where('work_action_id', 4)->orderBy('created_at', 'asc')->get();

        // 休憩のペアを作成
        $rests = [];
        foreach($rests_start as $index => $start) {
            $rests[] = [
                'start' => $start->created_at->format('H:i'),
                'end' => isset($rests_end[$index]) ? $rests_end[$index]->created_at->format('H:i'): null
            ];
        }

        return view('attendance_detail', compact('attendance', 'year', 'month', 'day', 'start_time', 'end_attendance', 'end_time', 'rests_start', 'rests_end', 'rests'));
    }
}
