<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class StampCorrectionController extends Controller
{
    public function correctionRequest()
    {
        // $correction = CorrectionRequest::findOrFail();
        $user = auth()->id();




        return view('stamp_correction_request');
    }
}
