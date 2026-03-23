<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class StampCorrectionController extends Controller
{
    public function correctionRequest(Request $request)
    {
        $userId = auth()->id();

        // 1.承認待ちの申請を表示（status = 0）
        $pendingRequests = CorrectionRequest::where('user_id', $userId)->where('status', CorrectionRequest::STATUS_PENDING)->get();
        // 2.承認済みの申請を表示（status = 0）
        $approveRequests = CorrectionRequest::where('user_id', $userId)->where('status', CorrectionRequest::STATUS_APPROVE)->get();

        return view('stamp_correction_request', compact('pendingRequests', 'approveRequests'));
    }
}
