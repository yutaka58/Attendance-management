<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;

use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // 管理者ログイン画面を表示のみ
    public function showLogin()
    {
        return view('auth.admin_login');
    }

    // 管理者ログイン機能
    public function adminLogin(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect('/admin/attendance/list');
            }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function AdminAttendanceList()
    {
        return view('admin_attendance_list'); // ビュー名は適宜合わせてください
    }
}
