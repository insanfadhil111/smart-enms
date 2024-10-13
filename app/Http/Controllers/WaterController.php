<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Water;
use App\Models\Subdata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaterController extends Controller
{
    private $id_dev_1;
    private $id_dev_2;

    public function __construct()
    {
        $this->id_dev_1 = 119;
        $this->id_dev_2 = 148;
    }

    public function monitor()
    {
        $title = 'Water Monitor';

        $latestData = Water::latest()->first();
        $nowFlow = $latestData->debit;
        $nowVolume = $latestData->volume;

        $monthly1 = $this->getThisAndLastMonthWater($this->id_dev_1);
        $monthly2 = $this->getThisAndLastMonthWater($this->id_dev_2);
        $thisMonth1 = $monthly1[0]->monthlyVol ?? 0;
        $thisMonth2 = $monthly2[0]->monthlyVol ?? 0;
        $lastMonth = $monthly1[1]->monthlyVol + $monthly2[1]->monthlyVol;
        $thisMonth = $thisMonth1 + $thisMonth2;
        $lastMonthCost = $monthly1[1]->monthlyCost + $monthly2[1]->monthlyCost;
        $thisMonthCost1 = $monthly1[0]->monthlyCost ?? 0;
        $thisMonthCost2 = $monthly2[0]->monthlyCost ?? 0;
        $thisMonthCost = $thisMonthCost1 + $thisMonthCost2;

        $daily_1 = $this->getTodayWater($this->id_dev_1);
        $daily_2 = $this->getTodayWater($this->id_dev_2);

        $today_1 = $daily_1[0]->todayVol;
        $today_2 = $daily_2[0]->todayVol;
        $today = $today_1 + $today_2;
        $latestUpdated_1 = $daily_1[0]->timestamp;
        $latestUpdated_2 = $daily_2[0]->timestamp;

        $decSep = Subdata::first()->decimal_sep;
        $thSep = Subdata::first()->thousand_sep;

        $data = [
            'nowFlow' => number_format($nowFlow, 2, $decSep, $thSep),
            'today' => number_format($today, 0, $decSep, $thSep),
            'thisMonth' => number_format($thisMonth, 0, $decSep, $thSep),
            'thisMonthCost' => number_format($thisMonthCost, 0, $decSep, $thSep),
            'lastMonth' => number_format($lastMonth, 0, $decSep, $thSep),
            'lastMonthCost' => number_format($lastMonthCost, 0, $decSep, $thSep),
        ];

        // if latestUpdated is not today give message "Tidak ada data hari ini"
        if (Carbon::parse($latestUpdated_1)->isToday() && Carbon::parse($latestUpdated_2)->isToday()) {
            $message = null;
        } else if (!Carbon::parse($latestUpdated_1)->isToday()) {
            $message = 'Data 1 tidak ada hari ini';
        } else if (!Carbon::parse($latestUpdated_2)->isToday()) {
            $message = 'Data 2 tidak ada hari ini';
        } else {
            $message = 'Kedua data tidak ada hari ini';
        }

        $chartData1 = $this->getThisWeekChartData($this->id_dev_1);
        $chartData2 = $this->getThisWeekChartData($this->id_dev_2);

        $barChartDataWeek = $this->getThisWeekChartAsDailyData();
        $barChartDataMonth = $this->getThisMonthChartAsDailyData();
        $barChartDataYear = $this->getThisYearChartAsMonthlyData();
        // return $barChartDataMonth;

        $names = ['Today Volume', 'This Month Volume', 'This Month Cost', 'Last Month Volume', 'Last Month Cost'];
        $units1 = ['m³', 'm³', 'IDR', 'm³', 'IDR'];
        $units2 = ['L', 'L', 'IDR', 'L', 'IDR'];
        $keys = ['today', 'thisMonth', 'thisMonthCost', 'lastMonth', 'lastMonthCost'];
        return view('pages.water.monitor', [
            'title' => $title,
            'latestUpdated' => $latestUpdated_1,
            'data' => $data,
            'names' => $names,
            'units1' => $units1,
            'units2' => $units2,
            'keys' => $keys,
            'nowFlow' => $nowFlow,
            'nowVolume' => $nowVolume,
            'message' => $message,
            'chartData1' => $chartData1,
            'chartData2' => $chartData2,
            'barChartDataWeek' => $barChartDataWeek,
            'barChartDataMonth' => $barChartDataMonth,
            'barChartDataYear' => $barChartDataYear,
        ]);
    }

    public function getTodayWater(int $id_dev)
    {
        $data = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $id_dev)
            ->whereIn('id', function ($query) use ($id_dev) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $id_dev)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->take(2)->get();

        $length = count($data);

        if ($length < 2) {
            $data[0] = (object)[
                'volume' => 0,
                'todayVol' => 0,
                'timestamp' => "2024-09-25 15:04:59"
            ];

            return $data;
        }

        for ($i = 0; $i < $length - 1; $i++) {
            $data[$i]->todayVol = $data[$i]->volume - $data[$i + 1]->volume;
        }
        $data[$length - 1]->todayVol = $data[$length - 1]->volume;
        $data->pop();
        return $data;
    }

    public function getDailyWater(int $id_dev)
    {
        $data = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $id_dev)
            ->whereIn('id', function ($query) use ($id_dev) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $id_dev)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $length = count($data);

        for ($i = 0; $i < $length - 1; $i++) {
            $data[$i]->todayVol = $data[$i]->volume - $data[$i + 1]->volume;
        }
        $data[$length - 1]->todayVol = $data[$length - 1]->volume;
        return $data;
    }

    public function getMonthlyWater(int $id_dev)
    {
        $data = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $id_dev)
            ->whereIn('id', function ($query) use ($id_dev) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $id_dev)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $length = count($data);
        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();

        for ($i = 0; $i < $length - 1; $i++) {
            $data[$i]->monthlyVol = $data[$i]->volume - $data[$i + 1]->volume;
            $data[$i]->monthlyCost = $data[$i]->monthlyVol * $hargaPdam; // Biaya PDAM
        }
        $data[$length - 1]->monthlyVol = $data[$length - 1]->volume;
        $data[$length - 1]->monthlyCost = $data[$length - 1]->monthlyVol * $hargaPdam; // Biaya PDAM

        return $data;
    }

    public function getThisAndLastMonthWater(int $id_dev)
    {
        $data = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $id_dev)
            ->whereIn('id', function ($query) use ($id_dev) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $id_dev)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'desc') // Terbaru lebih dulu
            ->take(3)->get();

        $length = count($data);
        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();

        /* If there is no last month yet*/
        if ($length < 2) {
            $data[1] = (object)[
                'volume' => 0,
                'monthlyVol' => 0,
                'monthlyCost' => 0
            ];

            return $data;
        }

        for ($i = 0; $i < $length - 1; $i++) {
            $data[$i]->monthlyVol = $data[$i]->volume - $data[$i + 1]->volume;
            $data[$i]->monthlyCost = $data[$i]->monthlyVol * $hargaPdam; // Biaya PDAM
        }
        $data[$length - 1]->monthlyVol = $data[$length - 1]->volume;
        $data[$length - 1]->monthlyCost = $data[$length - 1]->monthlyVol * $hargaPdam; // Biaya PDAM

        return $data;
    }

    public function getTodayChartData(int $id_dev)
    {
        $today = Carbon::today()->format('Y-m-d');
        $data = DB::table('waters')
            ->select('volume', 'created_at as timestamp')
            ->whereDate('created_at', $today)
            ->where('id_dev', $id_dev)
            ->latest()->get();

        $length = count($data);

        for ($i = 0; $i < $length - 1; $i++) {
            $data[$i]->todayVol = $data[$i]->volume - $data[$i + 1]->volume;
        }

        $data->pop();

        return $data;
    }

    public function getThisWeekChartData(int $id_dev)
    {
        /* Get Last 7 Days of daily volume for chart data */
        $data = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $id_dev)
            ->whereDate('created_at', '>=', Carbon::today()->subDays(6))
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();
        $length = count($data);

        for ($i = 1; $i < $length; $i++) {
            $data[$i]->nowVol = $data[$i]->volume - $data[$i - 1]->volume;
        }

        $latestDataBeforeLastweek = Water::select('volume')
            ->where('id_dev', $id_dev)
            ->whereDate('created_at', '<', Carbon::today()->subDays(6))
            ->latest()->first();
        if ($latestDataBeforeLastweek === null) {
            $latestDataBeforeLastweek = (object)['volume' => 0];
        }

        $data[0]->nowVol = $data[0]->volume - $latestDataBeforeLastweek->volume;

        return $data;
    }

    public function getThisWeekChartAsDailyData()
    {
        /* Get Last 7 Days of daily volume for chart data */
        $data1 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_1)
            ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_1)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $data2 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_2)
            ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_2)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $length1 = count($data1);
        $length2 = count($data2);
        /* Compare the length of those two data, then use the longer one. sum the value when the day is same and make it 0 to the one which doent have data for that day */
        $length = max($length1, $length2);
        for ($i = 0; $i < $length; $i++) {
            $day1 = isset($data1[$i]) ? $data1[$i]->day : null;
            $day2 = isset($data2[$i]) ? $data2[$i]->day : null;
            if ($day1 === $day2) {
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day1 === null) {
                $data1[$i] = (object) [
                    'day' => $day2,
                    'volume' => 0,
                    'vol_1' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = 0;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day2 === null) {
                $data2[$i] = (object) [
                    'day' => $day1,
                    'volume' => 0,
                    'vol_2' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = 0;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            }
        }

        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();

        for ($i = 1; $i < $length; $i++) {
            $data1[$i]->thisVol = $data1[$i]->totalVol - $data1[$i - 1]->totalVol;
            $data1[$i]->cost = $data1[$i]->thisVol * $hargaPdam;
            $data1[$i]->time = Carbon::parse($data1[$i]->day)->format('d M');
        }
        $data1->shift();
        return $data1;
    }

    public function getThisMonthChartAsDailyData()
    {
        $thisMonthStart = Carbon::today()->startOfMonth()->format('Y-m-d');

        $data1 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_1)
            ->whereDate('created_at', '>=', $thisMonthStart)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_1)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $data2 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as day'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_2)
            ->whereDate('created_at', '>=', $thisMonthStart)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_2)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), DAY(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $length1 = count($data1);
        $length2 = count($data2);
        /* Compare the length of those two data, then use the longer one. sum the value when the day is same and make it 0 to the one which doent have data for that day */
        $length = max($length1, $length2);
        for ($i = 0; $i < $length; $i++) {
            $day1 = isset($data1[$i]) ? $data1[$i]->day : null;
            $day2 = isset($data2[$i]) ? $data2[$i]->day : null;
            if ($day1 === $day2) {
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day1 === null) {
                $data1[$i] = (object) [
                    'day' => $day2,
                    'volume' => 0,
                    'vol_1' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = 0;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day2 === null) {
                $data2[$i] = (object) [
                    'day' => $day1,
                    'volume' => 0,
                    'vol_2' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = 0;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            }
        }

        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();

        for ($i = 1; $i < $length; $i++) {
            $data1[$i]->thisVol = $data1[$i]->totalVol - $data1[$i - 1]->totalVol;
            $data1[$i]->cost = $data1[$i]->thisVol * $hargaPdam;
            $data1[$i]->time = Carbon::parse($data1[$i]->day)->format('d M');
        }
        $data1->shift();
        return $data1;
    }

    public function getThisYearChartAsMonthlyData()
    {
        /* Mulai ambil dari desember tahun lalu karena volume Januari harus dikurangi Volume Desember */
        $thisYearStart = Carbon::today()->subYear()->endOfYear()->format('Y-m-d');
        // $thisYearStart = Carbon::today()->startOfYear()->format('Y-m-d');

        $data1 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_1)
            ->whereDate('created_at', '>=', $thisYearStart)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_1)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $data2 = Water::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            'volume',
            'created_at as timestamp'
        )
            ->where('id_dev', $this->id_dev_2)
            ->whereDate('created_at', '>=', $thisYearStart)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->where('id_dev', $this->id_dev_2)
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'asc')  // Terlama lebih dulu
            ->get();

        $length1 = count($data1);
        $length2 = count($data2);
        /* Compare the length of those two data, then use the longer one. sum the value when the day is same and make it 0 to the one which doent have data for that day */
        $length = max($length1, $length2);
        for ($i = 0; $i < $length; $i++) {
            $day1 = isset($data1[$i]) ? $data1[$i]->day : null;
            $day2 = isset($data2[$i]) ? $data2[$i]->day : null;
            if ($day1 === $day2) {
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day1 === null) {
                $data1[$i] = (object) [
                    'day' => $day2,
                    'volume' => 0,
                    'vol_1' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = 0;
                $data1[$i]->vol_2 = $data2[$i]->volume;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            } else if ($day2 === null) {
                $data2[$i] = (object) [
                    'day' => $day1,
                    'volume' => 0,
                    'vol_2' => 0,
                    'timestamp' => null
                ];
                $data1[$i]->vol_1 = $data1[$i]->volume;
                $data1[$i]->vol_2 = 0;
                $data1[$i]->totalVol = $data1[$i]->volume + $data2[$i]->volume;
            }
        }

        $hargaPdam = Subdata::latest()->pluck('hargaPdam')->first();

        for ($i = 1; $i < $length; $i++) {
            $data1[$i]->thisVol = $data1[$i]->totalVol - $data1[$i - 1]->totalVol;
            $data1[$i]->cost = $data1[$i]->thisVol * $hargaPdam;
            $data1[$i]->time = Carbon::parse($data1[$i]->month)->format('M Y');
        }
        $data1->shift(); // Menghapus volume desember tahun lalu
        return $data1;
    }

    public function addWaterData(Request $request)
    {
        try {
            $data = new Water();
            $data->id_dev = $request->id_dev;
            $data->debit = $request->debit;
            $data->volume = $request->volume;

            $data->save();
            return response()->json([
                'message' => 'Data added successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getWaterData(int $limit)
    {
        $data = Water::latest()->take($limit)->get();
        $formattedData = $data->map(function ($item) {
            $item->timestamp = $item->created_at->format('d-m-Y H:i:s');
            return $item;
        });
        $formattedData->makeHidden(['created_at', 'updated_at']);
        return $formattedData;
    }

    public function getThisYearWaterUsage(int $year)
    {
        $water_data = Water::select(
            DB::raw('DATE_FORMAT(created_at,"%Y") as tahun'),
            'volume',
            'id_dev',
            'created_at as timestamp'
        )
            ->whereIn('id_dev', [$this->id_dev_1, $this->id_dev_2])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->whereIn('id_dev', [$this->id_dev_1, $this->id_dev_2])
                    ->groupBy(DB::raw('id_dev, YEAR(created_at)'));
            })
            ->get()
            ->groupBy('tahun');

        $subdata = Subdata::latest()->first();
        $hargaPdam = $subdata->hargaPdam;
        $kwhEquivPerMeterKubik = $subdata->kwhAirPerMeterKubik;

        $groupedData = [];
        $lastYearTotal = null;

        foreach ($water_data as $tahun => $records) {
            $volume_1 = $records->where('id_dev', $this->id_dev_1)->sum('volume');
            $volume_2 = $records->where('id_dev', $this->id_dev_2)->sum('volume');
            $totalVol = $volume_1 + $volume_2;

            if ($lastYearTotal === null) {
                $annualVol = $totalVol;  // For the first year, annualVol is just totalVol
            } else {
                $annualVol = $totalVol - $lastYearTotal;
            }

            // Menghitung pelengkap
            $totalCost = $annualVol * $hargaPdam;
            $kwhAir = $annualVol * $kwhEquivPerMeterKubik;
            $co2eq = round($kwhAir * $subdata->co2eq, 2);

            $groupedData[$tahun] = [
                'volume_1' => $volume_1,
                'volume_2' => $volume_2,
                'totalVol' => $totalVol,
                'annualVol' => $annualVol,
                'annualCost' => $totalCost,
                'kwhAir' => $kwhAir, // in kWh
                'co2eq' => $co2eq // in kg CO2
            ];

            $lastYearTotal = $totalVol; // Update lastYearTotal for the next iteration
        }

        return $groupedData[$year];
    }

    public function getAnnualWaterUsage()
    {
        $water_data = Water::select(
            DB::raw('DATE_FORMAT(created_at,"%Y") as tahun'),
            'volume',
            'id_dev',
            'created_at as timestamp'
        )
            ->whereIn('id_dev', [$this->id_dev_1, $this->id_dev_2])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('waters')
                    ->whereIn('id_dev', [$this->id_dev_1, $this->id_dev_2])
                    ->groupBy(DB::raw('id_dev, YEAR(created_at)'));
            })
            ->get()
            ->groupBy('tahun');

        $subdata = Subdata::latest()->first();
        $hargaPdam = $subdata->hargaPdam;
        $kwhEquivPerMeterKubik = $subdata->kwhAirPerMeterKubik;

        $groupedData = [];
        $lastYearTotal = null;

        foreach ($water_data as $year => $records) {
            $volume_1 = $records->where('id_dev', $this->id_dev_1)->sum('volume');
            $volume_2 = $records->where('id_dev', $this->id_dev_2)->sum('volume');
            $totalVol = $volume_1 + $volume_2;

            if ($lastYearTotal === null) {
                $annualVol = $totalVol;  // For the first year, annualVol is just totalVol
            } else {
                $annualVol = $totalVol - $lastYearTotal;
            }

            // Menghitung pelengkap
            $totalCost = $annualVol * $hargaPdam;
            $kwhAir = $annualVol * $kwhEquivPerMeterKubik;
            $co2eq = round($kwhAir * $subdata->co2eq, 2);

            $groupedData[$year] = [
                'volume_1' => $volume_1,
                'volume_2' => $volume_2,
                'totalVol' => $totalVol,
                'annualVol' => $annualVol,
                'annualCost' => $totalCost,
                'kwhAir' => $kwhAir,
                'co2eq' => $co2eq
            ];

            $lastYearTotal = $totalVol; // Update lastYearTotal for the next iteration
        }

        return $groupedData;
    }
}
