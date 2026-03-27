<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CorrectionRequest;

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

        return redirect()->back();
    }
}
