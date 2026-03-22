<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function correctionRequest()
    {
        return view('stamp_correction_request');
    }
}
