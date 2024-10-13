<?php

namespace App\Http\Controllers;

use App\Models\AccountLog;
use Illuminate\Http\Request;

class AccountLogController extends Controller
{
    public function index()
    {
        $logs = AccountLog::with('user')->latest()->get();
        return view('pages.account-log', compact('logs'));
    }
}

