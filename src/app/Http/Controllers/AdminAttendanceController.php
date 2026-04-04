<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceDetailRequest;

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
        $date = $id;

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
        $isApproved = false;

        if ($attendance) {
                // 承認待ち(0)または承認済み(1)の最新申請を取得
                $latestRequest = CorrectionRequest::where('user_id', $userId)
                    ->where('attendance_id', $attendance->id)
                    ->whereIn('status', [0, 1])
                    ->latest()
                    ->first();

                if ($latestRequest) {
                    $correction = $latestRequest;
                    if ($latestRequest->status == 1) {
                        $isApproved = true; // ★承認済みフラグを立てる
                    }
                }
            }

            $rests = [];
            if($correction) {
                // 修正の申請があった場合（承認待ち・承認済みどちらも）
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

        if (!$correction && !$isApproved) {
            $rests[] = ['start' => '', 'end' => ''];
        }

        // 申請があればそれを優先、なければ元の打刻。どちらもなければ null
        $start_time = $correction ? $correction->start_time : ($attendance ? $attendance->created_at->format('H:i') : '');
        $end_time = $correction ? $correction->end_time : ($end_attendance ? $end_attendance->created_at->format('H:i') : '');

        $remarks = $attendance ? $attendance->remarks : '';

        return view('admin_attendance_detail', compact('attendance', 'year', 'month', 'day', 'start_time', 'end_time', 'rests', 'correction', 'currentDay', 'userId', 'user', 'remarks', 'date', 'isApproved'));
    }

    public function updateAttendance(AttendanceDetailRequest $request, $id)
    {
        $userId = $request->input('user_id');
        $date = $id;
        $user = User::find($userId);

        $rest_starts = $request->rest_start ?? [];
        $rest_ends = $request->rest_end ?? [];

        // --- 1. 出勤・退勤の更新用データを定義 ---
        $actions = [
            1 => $request->start_time, // 出勤
            2 => $request->end_time,   // 退勤
        ];

        // --- 2. 出勤・退勤の実行 ---
        foreach ($actions as $actionId => $time) {
            if ($time) {
                $newDateTime = Carbon::parse($date . ' ' . $time);

                // 既存レコードを検索
                $record = Attendance::where('user_id', $userId)
                    ->where('work_action_id', $actionId)
                    ->whereDate('created_at', $date)
                    ->first();

                if ($record) {
                    $record->update([
                        'created_at' => $newDateTime,
                        'user_name' => $user->name,
                    ]);
                } else {
                    Attendance::create([
                        'user_id' => $userId,
                        'user_name' => $user->name,
                        'work_action_id' => $actionId,
                        'created_at' => $newDateTime,
                    ]);
                }
            }
        }

        // --- 3. 休憩時間の更新（一度消して作り直す） ---
        Attendance::where('user_id', $userId)
            ->whereIn('work_action_id', [3, 4])
            ->whereDate('created_at', $date)
            ->delete();

        $valid_rest_starts = array_filter($rest_starts);
        foreach ($valid_rest_starts as $index => $start) {
            Attendance::create([
                'user_id' => $userId,
                'user_name' => $user->name,
                'work_action_id' => 3,
                'created_at' => Carbon::parse($date . ' ' . $start),
            ]);
            if (!empty($rest_ends[$index])) {
                Attendance::create([
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'work_action_id' => 4,
                    'created_at' => Carbon::parse($date . ' ' . $rest_ends[$index]),
                ]);
            }
        }

        // --- 4. 備考 範囲指定で検索 ---
        $targetDate = Carbon::parse($date);
        $attendanceRecord = Attendance::where('user_id', $userId)
            ->where('work_action_id', 1)
            ->whereBetween('created_at', [
                $targetDate->copy()->startOfDay(),
                $targetDate->copy()->endOfDay()
            ])
            ->first();

        if (!$attendanceRecord) {
            $attendanceRecord = new Attendance();
            $attendanceRecord->user_id = $userId;
            $attendanceRecord->work_action_id = 1;
            $attendanceRecord->created_at = $targetDate->copy()->setTimeFromTimeString($request->start_time);
        }

        $attendanceRecord->remarks = $request->input('remarks_column');
        $attendanceRecord->save();

        return redirect("/admin/attendance/staff/{$userId}");
    }

    public function staffList()
    {
        $users = User::all();

        return view('admin_staff_list', compact('users'));
    }

    public function staffAttendanceList(Request $request, $id)
    {
        // パラーメータがなければ今月を取得
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 追加：現在の月を「日付」として扱うための変数を定義（Bladeで使っている名前に合わせる）
        $currentDay = $currentMonth;

        $user = User::find($id);

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

        return view('admin_attendance_staff', compact('user', 'monthParam', 'currentDay', 'currentMonth', 'prevMonth', 'nextMonth', 'attendances', 'tempDay'));
    }

    public function correctionRequest(Request $request)
    {
        $userId = auth()->id();
        // 承認待ちがアクティブになるよう初期設定
        $page = $request->query('page', 'pending');

        // 1.承認待ちの申請を表示（status = 0）
        $pendingRequests = CorrectionRequest::where('user_id', $userId)->where('status', CorrectionRequest::STATUS_PENDING)->get();
        // 2.承認済みの申請を表示（status = 1）
        $approveRequests = CorrectionRequest::where('user_id', $userId)->where('status', CorrectionRequest::STATUS_APPROVE)->get();

        return view('stamp_correction_request', compact('page', 'userId', 'pendingRequests', 'approveRequests'));
    }

    public function showApprove(Request $request, $id)
    {
        // 1. 申請データを取得
        $correction = CorrectionRequest::with(['user', 'attendance'])->findOrFail($id);

        // 2. 日付を分割
        $targetDate = Carbon::parse($correction->attendance->created_at);
        $year = $targetDate->format('Y');
        $month = $targetDate->format('n');
        $day = $targetDate->format('j');

        // 3. 休憩データをViewの形式に整える
        $rests = [];
        $c_starts = json_decode($correction->rest_start, true) ?? [];
        $c_ends = json_decode($correction->rest_end, true) ?? [];
        foreach($c_starts as $index => $val) {
            if(!empty($val)) {
                $rests[] = ['start' => $val, 'end' => $c_ends[$index] ?? ''];
            }
        }

        // 4. 出勤・退勤時間を変数にセット
        $start_time = $correction->start_time;
        $end_time = $correction->end_time;

        return view('admin_stamp_correction_approve', compact('correction', 'year', 'month', 'day', 'rests', 'start_time', 'end_time'
        ));
    }

    public function csv(Request $request)
    {
        // 全件ではなく、work_action_id が 1 (出勤) のものだけを取得
        $attendances = Attendance::with('user')
            ->where('work_action_id', 1)
            ->get();

        $csvHeader = ['ユーザー名','年月日','出勤','退勤','休憩','合計'];
        $temps = [$csvHeader];

        foreach ($attendances as $attendance) {
            // 同じ日の「退勤(id:2)」レコードを探して、そこにある計算済みの値を取る
            $clockOutRecord = Attendance::where('user_id', $attendance->user_id)
                ->where('work_action_id', 2)
                ->whereDate('created_at', $attendance->created_at->toDateString())
                ->first();

            $temp = [
                $attendance->user->name,
                $attendance->created_at->format('Y/m/d'),
                $attendance->start_time,
                $clockOutRecord->end_time ?? '',
                $clockOutRecord->total_rest_time ?? '',
                $clockOutRecord->work_time ?? '',
            ];
            array_push($temps, $temp);
        }

        $stream = fopen('php://temp', 'r+b');
        foreach ($temps as $temp) {
            fputcsv($stream, $temp);
        }
        rewind($stream);

        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');

        $filename = "勤怠一覧_" . now()->format('Ymd') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }
}
