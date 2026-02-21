<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;

use App\Models\User;

use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 会員登録画面を表示させるのみ
    public function index()
    {
        return view('auth.register');
    }

    // 会員登録情報の保存処理
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return redirect('/attendance');
    }
}
