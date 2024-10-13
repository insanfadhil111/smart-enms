<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MdpKwh;
use App\Models\MdpControl;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PvController;
use App\Http\Controllers\MdpController;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $subCon = new SubdataController();
        /* Real Time Data */
        $MdpCon = new MdpController();
        $todayKwh = $MdpCon->totalMdpKwhToday();
        $PvCon = new PvController();
        $todayMpp = $PvCon->mppTodayGeneration()['totalEnergy'];
        $todayMppGenerated = $subCon->formatNumber($todayMpp, 2);
        $todayGw = $PvCon->gwTodayGeneration()['totalEnergy'];
        $todayGwGenerated = $subCon->formatNumber($todayGw, 2);

        $todayPv = $todayMpp + $todayGw;
        $todayIncome = $subCon->formatNumber($todayPv * 1440, 0);

        /* Grafik Monthly */
        $thisYear = Carbon::now()->year;
        $enCon = new EnmsReportController();
        $thisYearKwh = $enCon->getMonthlyKwhReport($thisYear);
        $monthlyKwh = [];
        $months = [];
        // dd($thisYearKwh);
        foreach ($thisYearKwh as $item) {
            array_push($monthlyKwh, $item->kwh);
            $f_month = Carbon::parse($item->timestamp)->format('M');
            array_push($months, $f_month);
        }

        /* Device Status */
        $items = MdpControl::oldest()->get();


        return view('pages.dashboard', compact('todayKwh', 'todayMppGenerated', 'todayGwGenerated', 'todayIncome', 'items', 'monthlyKwh', 'months'));
    }

    public function debugFunc()
    {
        $lastDataFromPLN = MdpKwh::latest()->first()->created_at; // Initial value
        $startTime = $lastDataFromPLN->copy()->addDay();
        $currentTime = Carbon::now();
        $diffInHours = $startTime->diffInHours($currentTime);
        dd($currentTime, $startTime, $diffInHours);
    }
}
