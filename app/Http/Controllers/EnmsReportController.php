<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MdpKwh;
use App\Models\Subdata;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\IkeController;
use App\Http\Controllers\WaterController;

class EnmsReportController extends Controller
{
    public function index()
    {
        $title = 'EnMS Report';

        $thisYear = Carbon::now()->year;
        $tarif = Subdata::latest()->pluck('hargaKwh')->first();
        $oldestYear = 2018;

        $thisYearKwh = MdpKwh::whereYear('created_at', $thisYear)->orderByDesc('updated_at')->first()->kwh_1 / 1000 ?? 0;

        /* 
            NOTE!
            Gonna need optimization for this
        */
        $donutChartData = [];
        $chartData = [];

        $wCon = new WaterController();

        for ($year = $oldestYear; $year <= $thisYear; $year++) {
            ${"kwh_$year"} = $this->getMonthlyKwhReport($year);

            $elecData = json_decode(${"kwh_$year"}, true);
            $co2eq = Subdata::latest()->pluck('co2eq')->first();
            $elecCost = round(collect($elecData)->sum('cost'), 0);  // Use collect() to make it work with sum()
            $elecKwh = round(collect($elecData)->sum('kwh'), 2);
            $elecToCo2eq = round($elecKwh * $co2eq / 1000, 2);

            // WaterData
            $waterData = $wCon->getThisYearWaterUsage($thisYear);
            $waterUsage = $waterData["annualVol"];
            $waterCost = $waterData["annualCost"];
            $waterCarbon = round($waterData["co2eq"] / 1000, 2); //in tonnes
            $waterKwh = $waterData["kwhAir"];

            $chartData[] = [
                $year => ${"kwh_$year"},
            ];
            $donutChartData[] = [
                $year => [
                    'cost' => [
                        'total' => $elecCost,
                        'breakdown' => [
                            'electricity' => $elecCost,
                            'water' => $waterCost,
                        ],
                        'projected' => $elecCost + $waterCost,
                        'increase' => 10,
                    ],
                    'consumption' => [
                        'total' => $elecKwh,
                        'breakdown' => [
                            'electricity' => $elecKwh,
                            'water' => $waterUsage,
                        ],
                        'projected' => $elecKwh + $waterUsage,
                        'increase' => 10,
                    ],
                    'carbon' => [
                        'total' => $elecToCo2eq,
                        'breakdown' => [
                            'electricity' => $elecToCo2eq,
                            'water' => $waterCarbon,
                        ],
                        'projected' => round($elecToCo2eq + $waterCarbon, 2),
                        'increase' => 10,
                    ],
                ]
            ];
        }

        return view('pages.enms_report.index', [
            'title' => $title,
            'thisYear' => $thisYear,
            'tarif' => $tarif,
            'oldestYear' => $oldestYear,
            'thisYearKwh' => $thisYearKwh,
            'donutChartData' => $donutChartData,
            'chartData' => $chartData,
        ]);
    }

    public function getMonthlyKwhReport($year)
    {
        $data = MdpKwh::select(
            DB::raw('DATE_FORMAT(created_at, "%m") as bulan, DATE_FORMAT(created_at, "%Y") as tahun'),
            'kwh_1',
            'created_at as timestamp'
        )
            ->whereYear('created_at', $year)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $length = count($data);
        $thresholdDate = Carbon::create(2024, 9, 1);
        $tarif = Subdata::latest()->pluck('hargaKwh')->first();
        $ikeCon = new IkeController();

        for ($i = 0; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            // Compare actual date to the threshold (09-2024)
            if ($currentDate->lt($thresholdDate)) {
                // Data before September 2024 (non-incremental)
                $data[$i]->kwh = $data[$i]->kwh_1;
                $ike = $ikeCon->ikeMonthlyClassification($data[$i]->kwh);
                $data[$i]->angka_ike = $ike['angka_ike'];
                $data[$i]->ike = $ike['ike'];
                $data[$i]->ike_color = $ike['color'];
            } else {
                // Data from September 2024 onward (incremental)
                $data[$i]->kwh = $data[$i]->kwh_1 - $data[$i - 1]->kwh_1;
                $ike = $ikeCon->ikeMonthlyClassification($data[$i]->kwh);
                $data[$i]->angka_ike = $ike['angka_ike'];
                $data[$i]->ike = $ike['ike'];
                $data[$i]->ike_color = $ike['color'];
            }
            $data[$i]->cost = $data[$i]->kwh * $tarif;
        }
        $data->makeHidden(['kwh_1']);

        // return response()->json($data);
        return $data;
    }

    public function getAnnualKwhReport()
    {
        $oldestYear = 2018;
        $thisYear = Carbon::now()->year;
        $annualData = [];
        $ikeCon = new IkeController();

        for ($year = $oldestYear; $year <= $thisYear; $year++) {
            ${"kwh_$year"} = $this->getMonthlyKwhReport($year);
            $consumption = ${"kwh_$year"}->sum('kwh_1');
            $cost = ${"kwh_$year"}->sum('cost');
            $ike = $ikeCon->ikeAnnualClassification($consumption);

            $annualData[] = [
                'year' => $year,
                'consumption' => $consumption,
                'cost' => $cost,
                'ike' => $ike['ike'],
                'ike_color' => $ike['color'],
            ];
        }
        return $annualData;
    }

    public function getDonutChartData($year)
    {
        $data = $this->getMonthlyKwhReport($year);
        $data = json_decode($data, true);

        $co2eq = Subdata::latest()->pluck('co2eq')->first();

        $cost = collect($data)->sum('cost');  // Use collect() to make it work with sum()
        $consumption = collect($data)->sum('kwh');
        $carbon = $consumption * $co2eq;

        $data = [
            'cost' => [
                'total' => $cost,
                'breakdown' => [
                    'electricity' => $cost,
                    'water' => 0,
                ],
                'projected' => $cost + 150000,
                'increase' => 10,
            ],
            'consumption' => [
                'total' => $consumption,
                'breakdown' => [
                    'electricity' => $consumption,
                    'water' => 0,
                ],
                'projected' => $consumption + 1000,
                'increase' => 10,
            ],
            'carbon' => [
                'total' => $carbon,
                'breakdown' => [
                    'electricity' => $carbon,
                    'water' => 0,
                ],
                'projected' => $carbon + 1000,
                'increase' => 10,
            ],
        ];
        return $data;
    }
}
