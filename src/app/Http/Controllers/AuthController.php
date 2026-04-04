<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    // 会員登録情報の保存処理
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 認証メール送信
        event(new \Illuminate\Auth\Events\Registered($user));

        // 認証画面へ
        return redirect()->route('verification.notice');
    }

    // ログイン画面
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user=Auth::user();

            // メール認証が済んでいない場合、認証画面へ
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // 済みの場合
            return redirect('/attendance');
            }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function certification()
    {
        return view('auth.certification');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/attendance');
    }
}
