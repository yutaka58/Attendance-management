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

    public function updateAttendance(AttendanceDetailRequest $request, $id)
    {
        $userId = $request->input('user_id');
        $date = $id;
        $user = User::find($userId);

        $rest_starts = $request->rest_start ?? [];
        $rest_ends = $request->rest_end ?? [];

        // --- 1. 休憩の前後関係バリデーション (先にやる) ---
        foreach ($rest_starts as $index => $start) {
            $end = $rest_ends[$index] ?? null;
            if (!empty($start) && !empty($end)) {
                if (strtotime($start) >= strtotime($end)) {
                    return back()->withErrors([
                        "rest_end.{$index}" => '休憩終了時間は開始時間より後にしてください'
                    ])->withInput();
                }
            }
        }

        // --- 2. 出勤・退勤の更新用データを定義 ---
        $actions = [
            1 => $request->start_time, // 出勤
            2 => $request->end_time,   // 退勤
        ];

        // --- 3. 出勤・退勤の実行 ---
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

        // --- 4. 休憩時間の更新（一度消して作り直す） ---
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

        // --- 5. リダイレクト ---
        return redirect("/admin/attendance/staff/{$userId}");
    }

    public function staffList()
    {
        $users = User::all();

        return view('admin_staff_list', compact('users'));
    }

    public function staffDetail()
    {
        return view('admin_attendance_staff');
    }
}
