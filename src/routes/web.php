<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

use App\Http\Controllers\AdminAuthController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 一般ユーザー
Route::get('/register', [AuthController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

// 認証ルート
Route::middleware('auth', 'verified')->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout']);

    // 一般　出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'attendance']);
});


// 管理者
Route::get('/admin/login', [AdminAuthController::class, 'showLogin']);
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);

Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AdminAuthController::class, 'AdminAttendanceList']);

});

