<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\WorkStatus;
use App\Models\WorkAction;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $work_status = WorkStatus::first(); // 現在の状態を取得

        // 現在のステータスに応じて、表示するボタンを固定
        $display_actions = [];

        if ($work_status->name ==='勤務外') {
            $display_actions = WorkAction::where('name', '出勤')->get();
        } elseif ($work_status->name === '出勤中') {
            $display_actions = WorkAction::where('name', ['退勤', '休憩入'])->get();
        } elseif ($work_status->name === '休憩中') {
            $display_actions = WorkAction::where('name', '休憩戻')->get();
        } elseif ($work_status->name === '退勤済') {
            $display_actions = WorkAction::where('name', 'お疲れ様でした。')->get();
        }

        $work_actions = WorkAction::all();

        $dt = Carbon::today()->isoFormat('YYYY年M月D日(ddd)');

        $time = Carbon::now()->format('H:i');

        return view('/attendance', compact('work_status', 'dt', 'time', 'work_actions', 'display_actions'));
    }
}
