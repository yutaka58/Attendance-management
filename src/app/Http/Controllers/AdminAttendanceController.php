<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
        public function adminAttendanceList()
    {
        return view('admin_attendance_list');
    }
}
