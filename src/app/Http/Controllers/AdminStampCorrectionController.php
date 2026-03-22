<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStampCorrectionController extends Controller
{
    public function admin_correction_request()
    {
        return view('admin_stamp_correction_request_list');
    }
}
