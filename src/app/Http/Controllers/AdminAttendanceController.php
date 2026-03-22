<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attendance;

use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
        public function showAttendanceList(Request $request)
    {
        // パラーメータがなければ今月を取得
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::now();

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

        return view('admin_attendance_list', compact('monthParam', 'currentMonth', 'prevMonth', 'nextMonth', 'attendances', 'tempDay'));
    }

    public function adminDetail()
    {
        
        return view('admin_attendance_detail');
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
