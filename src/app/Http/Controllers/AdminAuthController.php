<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;

class AdminAuthController extends Controller
{
    public function adminLogin()
    {
        return view('auth.admin_login');
    }
}
