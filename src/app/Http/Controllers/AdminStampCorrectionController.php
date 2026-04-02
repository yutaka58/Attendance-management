<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

use Carbon\Carbon;

use Illuminate\Http\Request;

class AdminStampCorrectionController extends Controller
{
    public function adminCorrection(Request $request)
    {
        // 承認待ちがアクティブになるよう初期設定
        $page = $request->query('page', 'pending');

        // 1.承認待ちの申請を表示（status = 0）
        $pendingRequests = CorrectionRequest::with('user')->where('status', CorrectionRequest::STATUS_PENDING)->get();
        // 2.承認済みの申請を表示（status = 1）
        $approveRequests = CorrectionRequest::with('user')->where('status', CorrectionRequest::STATUS_APPROVE)->get();

        return view('admin_stamp_correction_request_list', compact('page','pendingRequests','approveRequests'));
    }

    // 画面表示用
    public function showApprove($attendance_correct_request_id)
    {
        // 1. 修正申請データを取得
        $correction = CorrectionRequest::findOrFail($attendance_correct_request_id);

        // 2. 紐づく勤怠データを取得
        $attendance = $correction->attendance;

        // 3. 日付の表示用
        $targetDate = \Carbon\Carbon::parse($attendance->created_at);
        $year = $targetDate->format('Y');
        $month = $targetDate->format('n');
        $day = $targetDate->format('j');

        // 4. 休憩データの構築
        $rests = [];
        $c_starts = json_decode($correction->rest_start, true) ?? [];
        $c_ends = json_decode($correction->rest_end, true) ?? [];
            foreach($c_starts as $index => $val) {
                if(!empty($val)) {
                    $rests[] = ['start' => $val, 'end' => $c_ends[$index] ?? ''];
                }
            }

        // ★ 常に空の休憩枠を1つ追加する
        $rests[] = ['start' => '', 'end' => ''];

        // 5. 表示用の出退勤時間
        $start_time = $correction->start_time;
        $end_time = $correction->end_time;

        return view('admin_stamp_correction_approve', compact('correction', 'attendance', 'year', 'month', 'day', 'rests', 'start_time', 'end_time'));
    }

    public function approveUpdate(Request $request, $attendance_correct_request_id)
    {
        // 1. 修正申請データを取得
        $correction = CorrectionRequest::findOrFail($attendance_correct_request_id);

        // 2. 紐づく勤怠データを取得
        $attendance = $correction->attendance;

        if ($attendance) {
            $attendance->update ([
                'start_time' => $correction->start_time,
                'end_time' => $correction->end_time,
                'rest_start' => $correction->rest_start,
                'rest_end' => $correction->rest_end,
            ]);
        }

        $correction->update(['status'=> 1]);
        $correction->save();

        // 本体テーブルへの同期を実行
        $this->syncAttendanceData($correction);

        return redirect()->back();
    }

    private function syncAttendanceData($correction)
    {
        $attendanceId = $correction->attendance_id;
        $userId = $correction->user_id;
        $user = User::find($userId);

        // 1. 元の出勤レコードを取得
        $baseAttendance = Attendance::find($attendanceId);
        if (!$baseAttendance) return;

        // 元の「日」を保持（検索用）
        $originalDate = $baseAttendance->created_at->format('Y-m-d');

        // 2. 出勤時間の更新
        $baseAttendance->update([
            'created_at' => Carbon::parse($originalDate . ' ' . $correction->start_time),
        ]);
    
        // 3. 退勤時間の更新（「同じ日の退勤アクション」を確実に特定）
        Attendance::where('user_id', $userId)
            ->where('work_action_id', 2)
            ->whereDate('created_at', $originalDate) // 修正前の日付で検索
            ->update([
                'created_at' => Carbon::parse($originalDate . ' ' . $correction->end_time),
            ]);

        // 4. 休憩時間の再構築（一旦削除して作り直し）
        Attendance::where('user_id', $userId)
            ->whereIn('work_action_id', [3, 4])
            ->whereDate('created_at', $originalDate)
            ->delete();

        $rest_starts = json_decode($correction->rest_start, true) ?? [];
        $rest_ends = json_decode($correction->rest_end, true) ?? [];

        foreach ($rest_starts as $index => $start) {
            if (!empty($start)) {
                Attendance::create([
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'work_action_id' => 3,
                    'created_at' => Carbon::parse($originalDate . ' ' . $start),
                ]);
                if (!empty($rest_ends[$index])) {
                    Attendance::create([
                        'user_id' => $userId,
                        'user_name' => $user->name,
                        'work_action_id' => 4,
                        'created_at' => Carbon::parse($originalDate . ' ' . $rest_ends[$index]),
                    ]);
                }
            }
        }
    }
}
