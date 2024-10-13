<?php

namespace App\Http\Controllers;

use App\Models\AccountLog;
use Illuminate\Http\Request;
use PDF; // Pastikan untuk mengimport PDF

class AccountLogController extends Controller
{
    public function index(Request $request)
    {
        // Ambil log dengan filter dan sorting
        $logs = $this->applyFilters($request);
        return view('pages.account-log', compact('logs'));
    }

    public function exportPDF(Request $request)
    {
        // Ambil log dengan filter dan sorting
        $logs = $this->applyFilters($request);

        $pdf = PDF::loadView('pdf.account-logs', compact('logs'));
        return $pdf->download('account_logs.pdf');
    }

    protected function applyFilters(Request $request)
    {
        $query = AccountLog::with('user');

        // Filter berdasarkan pilihan
        if ($request->filter === 'newest') {
            $query->latest()->take(10);
        } elseif ($request->filter === 'oldest') {
            $query->oldest()->take(10);
        }

        // Sortir berdasarkan pilihan
        if ($request->sort_by) {
            if ($request->sort_by === 'user') {
                $query->join('users', 'account_logs.user_id', '=', 'users.id')
                    ->orderBy('users.firstname', $request->sort_order);
            } elseif ($request->sort_by === 'email') {
                $query->join('users', 'account_logs.user_id', '=', 'users.id')
                    ->orderBy('users.email', $request->sort_order);
            } elseif ($request->sort_by === 'login_time') {
                $query->orderBy('account_logs.login_time', $request->sort_order);
            } elseif ($request->sort_by === 'created_at') { // Menambahkan pengurutan berdasarkan created_at
                $query->orderBy('account_logs.created_at', $request->sort_order);
            }
        } else {
            $query->latest(); // Default sorting jika tidak ada yang dipilih
        }

        return $query->get();
    }


}
