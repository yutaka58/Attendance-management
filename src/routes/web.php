<?php

// 一般
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionController;

// 管理者
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStampCorrectionController;


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
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

// メール認証
Route::get('/certification', [AuthController::class, 'certification'])->name('verification.notice');
// 認証後
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');

// 一般　認証ルート
Route::middleware(['auth', 'verified'])->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout']);

    // 一般　出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'attendance']);
    Route::post('/attendance', [AttendanceController::class, 'storeAttendance']);

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList']);

    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'attendanceDetail']);
    // 勤怠詳細画面　修正依頼
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'timeCorrection']);

    // 申請一覧画面
    Route::get('/stamp_correction_request/list', [StampCorrectionController::class, 'correctionRequest']);
});


// 管理者　認証ルート
Route::get('/admin/login', [AdminAuthController::class, 'showLogin']);
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);

Route::middleware(['auth:admin'])->group(function () {
    // ログアウト
    Route::post('/admin/logout', [AdminAuthController::class, 'adminLogout']);

    // 勤怠一覧画面
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'showAttendanceList']);
    // 勤怠詳細画面
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'adminDetail']);
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'updateAttendance']);

    // スタッフ一覧画面
    Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staffList']);
    // スタッフ別勤怠一覧画面
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendanceList']);

    // 申請一覧画面
    Route::get('/admin/stamp_correction_request/list', [AdminStampCorrectionController::class, 'adminCorrection']);

    // 修正申請承認画面
    Route::get('/admin/stamp_correction_request/approve/{id}', [AdminStampCorrectionController::class, 'showApprove']);
    Route::post('/admin/stamp_correction_request/approve/{id}', [AdminStampCorrectionController::class, 'approveUpdate']);
});

