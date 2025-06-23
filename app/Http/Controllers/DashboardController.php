<?php

namespace App\Http\Controllers;

use App\Models\LogQr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $todayLogs = LogQr::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get();

        return view('dashboard', compact('todayLogs'));
    }
}
