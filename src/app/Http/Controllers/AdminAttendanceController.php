<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attendance;

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
