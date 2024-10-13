<?php

namespace App\Http\Controllers;

use App\Models\MdpKwh;
use GuzzleHttp\Client;
use App\Models\Subdata;
use App\Models\MdpControl;
use Illuminate\Http\Request;
use App\Models\EnergyPredict;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\IkeController;
use App\Http\Controllers\MdpController;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\EnmsReportController;
use App\Http\Controllers\MdpSyahrulController;
use Illuminate\Pagination\LengthAwarePaginator;

class EnergyController extends Controller
{
    public function monitor()
    {
        $title = 'Energy Monitoring';
        $mdpCon = new MdpController();

        // Latest Realtime Data
        $mdp = $mdpCon->getLatestMdpDataById(1, 1); // Total
        $ac1 = $mdpCon->getLatestMdpDataById(2, 1);
        $ac2 = $mdpCon->getLatestMdpDataById(3, 1);
        $util1 = $mdpCon->getLatestMdpDataById(4, 1);
        $util2 = $mdpCon->getLatestMdpDataById(5, 1);

        $latestUpdatedAc1 = $ac1->created_at;
        $latestUpdatedAc2 = $ac2->created_at;
        $latestUpdatedUtil1 = $util1->created_at;
        $latestUpdatedUtil2 = $util2->created_at;
        $latestUpdatedMdp = $mdp->created_at;

        /* Data dari MDP Mas Hisbul */
        $mdpEn = $this->getEnergyUsageMonitorById(1);
        $ac1En = $this->getEnergyUsageMonitorById(2);
        $ac2En = $this->getEnergyUsageMonitorById(3);
        $util1En = $this->getEnergyUsageMonitorById(4);
        $util2En = $this->getEnergyUsageMonitorById(5);

        $decSep = Subdata::latest()->pluck('decimal_sep')->first();
        $thSep = Subdata::latest()->pluck('thousand_sep')->first();

        $chartData = $mdpCon->getMdpChartData();
        // return $chartData;


        $collection = ['Voltage A-N', 'Voltage B-N', 'Voltage C-N', 'Current A', 'Current B', 'Current C', 'Total Current', 'Active Power A', 'Active Power B', 'Active Power C', 'Total Active P', 'Power Factor', 'Frequency', 'Reactive Power A', 'Reactive Power B', 'Reactive Power C', 'Total Reactive P'];
        $keys = ['Van', 'Vbn', 'Vcn', 'Ia', 'Ib', 'Ic', 'It', 'Pa', 'Pb', 'Pc', 'Pt', 'pf', 'f', 'Qa', 'Qb', 'Qc', 'Qt'];
        $keysEn = ['todayKwh', 'thisMonthKwh', 'thisMonthCost', 'lastMonthKwh', 'lastMonthCost'];
        $units = ['V', 'V', 'V', 'A', 'A', 'A', 'A', 'W', 'W', 'W', 'W', '', 'Hz', 'VAR', 'VAR', 'VAR', 'VAR'];
        $collection2 = ["Today", "This Month", "This Month Cost", "Last Month", "Last Month Cost"];
        $units2 = ['kWh', 'kWh', 'IDR', 'kWh', 'IDR'];

        return view("pages.energy.monitor", [
            'title' => $title,
            'keys' => $keys,
            'units' => $units,
            'collection' => $collection,
            'ac1' => $ac1,
            'ac2' => $ac2,
            'util1' => $util1,
            'util2' => $util2,
            'mdp' => $mdp,
            'latestUpdatedAc1' => $latestUpdatedAc1,
            'latestUpdatedAc2' => $latestUpdatedAc2,
            'latestUpdatedUtil1' => $latestUpdatedUtil1,
            'latestUpdatedUtil2' => $latestUpdatedUtil2,
            'latestUpdatedMdp' => $latestUpdatedMdp,
            'mdpEn' => $mdpEn,
            'ac1En' => $ac1En,
            'ac2En' => $ac2En,
            'util1En' => $util1En,
            'util2En' => $util2En,
            'keysEn' => $keysEn,
            'decSep' => $decSep,
            'thSep' => $thSep,
            'units2' => $units2,
            'collection2' => $collection2,
            'chartData' => $chartData
        ]);
    }

    public function showControl()
    {
        $title = 'Energy Control';
        $items = MdpControl::oldest()->get();

        return view("pages.energy.control", compact('title', 'items'));
    }

    public function stats()
    {
        $title = 'Energy Statistic';

        /*  
            TEMPORARY
            Seeding EnergyPredict every Page Opened
        */
        $lastWeek = Carbon::today()->subDays(7);
        $latestPredict = EnergyPredict::latest()->first();

        // if ($latestPredict == null || $latestPredict->created_at->lt($lastWeek) || $latestPredict->created_at->eq($lastWeek)) {
        //     $data = [];
        //     $startDate = Carbon::today();

        //     for ($i = 0; $i < 7; $i++) {
        //         $data[] = [
        //             'date' => $startDate->copy()->addDays($i)->format('Y-m-d'),
        //             'prediction' => rand(200, 800), // Random prediction between 1000 and 5000
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ];
        //     }

        //     DB::table('energy_predicts')->insert($data);
        // }



        $thisYear = Carbon::now()->year;
        $daily = $this->getDailyEnergyReversed($thisYear);

        foreach ($daily as $item) {
            $date = Carbon::parse($item->date)->format('M j');
            $item->x = $date;
            $item->y = $item->total;
        }
        $daily->makeHidden(['date', 'timestamp', 'kwh1', 'kwh2', 'kwh3', 'kwh4', 'kwh5', 'total']);

        $predicts = $this->getWeeklyPrediction();
        $predicts->makeHidden(['id', 'created_at', 'updated_at']);
        foreach ($predicts as $item) {
            $date = Carbon::parse($item->date)->format('M j');
            $item->x = $date;
            $item->y = $item->prediction;
        }
        $predicts->makeHidden(['date', 'prediction']);

        // Selisih antara energi hari ini dengen kebiasaan di hari yang sama sebelumnya
        // $dailyEnergy = $this->getDailyEnergy();
        $todayKwh = $daily[count($daily) - 1]->total;
        $todayWeekday = Carbon::today()->dayOfWeek;
        $todayName = Carbon::today()->format('l');

        $previousEnergies = collect($daily)->filter(function ($energy) use ($todayWeekday) {
            $energyWeekday = Carbon::parse($energy->date)->dayOfWeek;
            return $energyWeekday === $todayWeekday && $energy->date < Carbon::today()->format('Y-m-d');
        });

        // Calculate the average energy consumption on same day in previous week
        $averageEnergy = $previousEnergies->avg('total') ?? 0; // jika tidak ada data sebelumnya, maka gunakan data hari ini
        $comparison = $todayKwh - $averageEnergy;

        $energyDiff = number_format(($comparison / $averageEnergy * 100), 2);
        $energyDiffStatus = ($todayKwh > $averageEnergy) ? 'naik' : 'turun';

        // Biaya listrik tiap bulan
        $monthlyKwh = $this->getMonthlyEnergy();

        $subCon = new SubdataController();

        $n = count($monthlyKwh);
        if ($n > 1) {
            for ($i = 1; $i < $n; $i++) {
                $monthlyKwh[$i]->diffStatus = ($monthlyKwh[$i]->total > $monthlyKwh[$i - 1]->total) ? 'naik' : 'turun';
                $monthlyKwh[$i]->diff = $subCon->formatNumber(abs(($monthlyKwh[$i]->total - $monthlyKwh[$i - 1]->total) / $monthlyKwh[$i - 1]->total) * 100, 2);
            }
        }
        $monthlyKwh[0]->diffStatus = 'turun';
        $monthlyKwh[0]->diff = 0;

        // Paginate the result manually
        $perPage = 6; // 6 items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = array_slice($monthlyKwh->toArray(), ($currentPage - 1) * $perPage, $perPage);

        // Convert array to collection for paginator
        $dataCollection = new Collection($currentPageItems);
        $request = new Request();

        // Create paginator instance
        $paginatedData = new LengthAwarePaginator(
            $currentPageItems, // Paginated items for the current page
            count($monthlyKwh), // Total count of items
            $perPage, // Items per page
            $currentPage, // Current page number
            [
                'path' => route('energy-stats'), // Get the current URL
                'query' => $request->query(), // Maintain the existing query string
            ]
        );

        return view("pages.energy.stats", compact('title', 'energyDiff', 'energyDiffStatus', 'todayName', 'predicts', 'daily', 'monthlyKwh', 'paginatedData'));
    }

    public function standarIke()
    {
        $title = 'IKE Standard';

        $monthly = $this->getMonthlyEnergy();

        /* Energy Usage Total */
        $thisMonthKwh = $monthly[0]->kwh_1;
        $thisMonthCost = $monthly[0]->bill;
        $lastMonthKwh = $monthly[1]->kwh_1;
        $lastMonthCost = $monthly[1]->bill;
        $names = ['This Month', 'This Month Cost', 'Last Month', 'Last Month Cost'];
        $values = [$thisMonthKwh, $thisMonthCost, $lastMonthKwh, $lastMonthCost];
        $units = ['kWh', 'IDR', 'kWh', 'IDR'];

        /* Chart Monthly */
        $thisYear = Carbon::now()->year;
        $tarif = Subdata::latest()->pluck('hargaKwh')->first();;
        $oldestYear = 2018;

        $enmsCon = new EnmsReportController();
        $monthlyChartData = [];

        for ($year = $oldestYear; $year <= $thisYear; $year++) {
            ${"kwh_$year"} = $enmsCon->getMonthlyKwhReport($year);
            $monthlyChartData[] = [
                $year => ${"kwh_$year"},
            ];
        }
        // return $monthlyChartData;

        /* Chart Annual */
        $annualChartData = $enmsCon->getAnnualKwhReport();
        // return $annualChartData;

        return view("pages.ike.index", [
            'title' => $title,
            'monthly' => $monthly,
            'names' => $names,
            'values' => $values,
            'units' => $units,
            'monthlyChartData' => $monthlyChartData,
            'annualChartData' => $annualChartData
        ]);
    }


    /* 
        for APIs
    */

    /**
     * Retrieve energy data with latest first
     *
     * @return Illuminate\Support\Collection
     */
    public function getDailyEnergy()
    {
        $data = MdpKwh::select(
            DB::raw('DATE(created_at) as date'),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('DATE(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $length = count($data);
        $ikeCon = new IkeController();
        $thresholdDate = Carbon::create(2024, 9, 1);

        for ($i = 0; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->kwh1 = round($data[$i]->kwh_1 / 30, 2);
                $data[$i]->kwh2 = round($data[$i]->kwh_2 / 30, 2);
                $data[$i]->kwh3 = round($data[$i]->kwh_3 / 30, 2);
                $data[$i]->kwh4 = round($data[$i]->kwh_4 / 30, 2);
                $data[$i]->kwh5 = round($data[$i]->kwh_5 / 30, 2);
            } else {
                $data[$i]->kwh1 = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
                $data[$i]->kwh2 = $data[$i]->kwh_2 - $data[$i + 1]->kwh_2;
                $data[$i]->kwh3 = $data[$i]->kwh_3 - $data[$i + 1]->kwh_3;
                $data[$i]->kwh4 = $data[$i]->kwh_4 - $data[$i + 1]->kwh_4;
                $data[$i]->kwh5 = $data[$i]->kwh_5 - $data[$i + 1]->kwh_5;
            }

            $data[$i]->total = $data[$i]->kwh1;

            $ike = $ikeCon->ikeMonthlyClassification($data[$i]->total * 30);
            $data[$i]->angka_ike = $ike['angka_ike'];
            $data[$i]->ike = $ike['ike'];
            $data[$i]->color = $ike['color'];
        }

        $data->pop();
        $data->makeHidden(['kwh_1', 'kwh_2', 'kwh_3', 'kwh_4', 'kwh_5']);

        return $data;
    }

    public function getDailyEnergyReversed(int $year)
    {
        /**
         * Oldest First
         *
         * @return Illuminate\Support\Collection
         */
        $data = MdpKwh::select(
            DB::raw('DATE(created_at) as date'),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereYear('created_at', $year)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('DATE(created_at)'));
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $length = count($data);
        $ikeCon = new IkeController();
        $thresholdDate = Carbon::create(2024, 10, 5);

        for ($i = 1; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->date);

            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->kwh1 = round($data[$i]->kwh_1 / 30, 2);
                $data[$i]->kwh2 = round($data[$i]->kwh_2 / 30, 2);
                $data[$i]->kwh3 = round($data[$i]->kwh_3 / 30, 2);
                $data[$i]->kwh4 = round($data[$i]->kwh_4 / 30, 2);
                $data[$i]->kwh5 = round($data[$i]->kwh_5 / 30, 2);
            }
            // Else if the current date is the threshold date
            else if ($currentDate->eq($thresholdDate)) {
                $data[$i]->kwh1 = $data[$i]->kwh_1;
                $data[$i]->kwh2 = $data[$i]->kwh_2;
                $data[$i]->kwh3 = $data[$i]->kwh_3;
                $data[$i]->kwh4 = $data[$i]->kwh_4;
                $data[$i]->kwh5 = $data[$i]->kwh_5;
            } else if ($currentDate->gt($thresholdDate)) {
                $data[$i]->kwh1 = $data[$i]->kwh_1 - $data[$i - 1]->kwh_1;
                $data[$i]->kwh2 = $data[$i]->kwh_2 - $data[$i - 1]->kwh_2;
                $data[$i]->kwh3 = $data[$i]->kwh_3 - $data[$i - 1]->kwh_3;
                $data[$i]->kwh4 = $data[$i]->kwh_4 - $data[$i - 1]->kwh_4;
                $data[$i]->kwh5 = $data[$i]->kwh_5 - $data[$i - 1]->kwh_5;
            }

            $data[$i]->total = $data[$i]->kwh1;

            $ike = $ikeCon->ikeMonthlyClassification($data[$i]->total * 30);
            $data[$i]->angka_ike = $ike['angka_ike'];
            $data[$i]->ike = $ike['ike'];
            $data[$i]->color = $ike['color'];
        }

        $data->shift();
        $data->makeHidden(['kwh_1', 'kwh_2', 'kwh_3', 'kwh_4', 'kwh_5']);

        return $data;
    }

    public function getLimitedDailyEnergy()
    {
        $data = MdpKwh::select(
            DB::raw('DATE(created_at) as date'),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('DATE(created_at)'));
            })
            ->orderBy('created_at', 'desc')->take(7)
            ->get();

        $length = count($data);
        $ikeCon = new IkeController();
        $thresholdDate = Carbon::create(2024, 9, 1);

        for ($i = 0; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->kwh_1 = round($data[$i]->kwh_1 / 30, 2);
                $data[$i]->kwh_2 = round($data[$i]->kwh_2 / 30, 2);
                $data[$i]->kwh_3 = round($data[$i]->kwh_3 / 30, 2);
                $data[$i]->kwh_4 = round($data[$i]->kwh_4 / 30, 2);
                $data[$i]->kwh_5 = round($data[$i]->kwh_5 / 30, 2);
            } else {
                $data[$i]->kwh_1 = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
                $data[$i]->kwh_2 = $data[$i]->kwh_2 - $data[$i + 1]->kwh_2;
                $data[$i]->kwh_3 = $data[$i]->kwh_3 - $data[$i + 1]->kwh_3;
                $data[$i]->kwh_4 = $data[$i]->kwh_4 - $data[$i + 1]->kwh_4;
                $data[$i]->kwh_5 = $data[$i]->kwh_5 - $data[$i + 1]->kwh_5;
            }

            $data[$i]->total = $data[$i]->kwh_1;
        }

        $data->pop();
        // $data->makeHidden(['kwh_1', 'kwh_2', 'kwh_3', 'kwh_4', 'kwh_5']);

        return $data;
    }

    public function getMonthlyEnergy()
    {
        $data = MdpKwh::select(
            DB::raw('DATE_FORMAT(created_at, "%m") as bulan, DATE_FORMAT(created_at, "%Y") as tahun '),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $price = Subdata::latest()->pluck('hargaKwh')->first();;
        $length = count($data);
        $ikeCon = new IkeController();
        $thresholdDate = Carbon::create(2024, 9, 1);

        if ($length == 1) {
            // Handle if there is no data[$i+1]
            $data[0]->total = number_format(($data[0]->kwh_1) / 1000, 2, '.', ',');
            $data[0]->bill = number_format($data[0]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[0]->bulan = Carbon::create(null, $data[0]->bulan)->monthName;
            $ike = $ikeCon->ikeMonthlyClassification($data[0]->total);
            $data[0]->angka_ike = $ike['angka_ike'];
            $data[0]->ike = $ike['ike'];
            $data[0]->color = $ike['color'];

            return $data;
        }

        for ($i = 0; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            // Data inject sebelum Sept 2024
            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->total = $data[$i]->kwh_1;
            } else {
                $data[$i]->total = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
            }

            $ike = $ikeCon->ikeMonthlyClassification($data[$i]->total);
            $data[$i]->angka_ike = $ike['angka_ike'];
            $data[$i]->ike = $ike['ike'];
            $data[$i]->color = $ike['color'];

            $data[$i]->bill = number_format($data[$i]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[$i]->bulan = Carbon::create(null, $data[$i]->bulan)->monthName;
        }

        $data->makeHidden(['kwh_1', 'kwh_2', 'kwh_3', 'kwh_4', 'kwh_5']);

        return $data;
    }

    public function getLimitedMonthlyEnergy($year)
    {
        $data = MdpKwh::select(
            DB::raw('DATE_FORMAT(created_at, "%m") as bulan, DATE_FORMAT(created_at, "%Y") as tahun '),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereYear('created_at', $year)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $price = Subdata::latest()->pluck('hargaKwh')->first();;
        $length = count($data);
        $thresholdDate = Carbon::create(2024, 9, 1);

        if ($length == 1) {
            // Handle if there is no data[$i+1]
            $data[0]->total = number_format(($data[0]->kwh_1) / 1000, 2, '.', ',');
            $data[0]->bill = number_format($data[0]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[0]->bulan = Carbon::create(null, $data[0]->bulan)->monthName;

            return $data;
        }

        for ($i = 0; $i < $length - 1; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            // Data inject sebelum Sept 2024
            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->total = $data[$i]->kwh_1;
            } else {
                $data[$i]->total = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
            }

            $data[$i]->bill = number_format($data[$i]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[$i]->bulan = Carbon::create(null, $data[$i]->bulan)->monthName;
        }

        $data->makeHidden(['kwh_1', 'kwh_2', 'kwh_3', 'kwh_4', 'kwh_5']);

        return $data;
    }

    public function getLastTwoMonthsEnergyById(int $id_kwh)
    {
        $data = MdpKwh::select(
            DB::raw('DATE_FORMAT(created_at, "%m") as bulan, DATE_FORMAT(created_at, "%Y") as tahun '),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
            })
            ->orderBy('created_at', 'desc')->take(3)
            ->get();

        $price = Subdata::latest()->pluck('hargaKwh')->first();;
        $length = count($data);
        $thresholdDate = Carbon::create(2024, 9, 1);
        // Handle if there is no data[$i+1]
        if ($length == 1) {
            $data[0]->kwh_1 = number_format(($data[0]->kwh_1) / 1000, 2, '.', ',');
            $data[0]->kwh_2 = number_format(($data[0]->kwh_2) / 1000, 2, '.', ',');
            $data[0]->kwh_3 = number_format(($data[0]->kwh_3) / 1000, 2, '.', ',');
            $data[0]->kwh_4 = number_format(($data[0]->kwh_4) / 1000, 2, '.', ',');
            $data[0]->kwh_5 = number_format(($data[0]->kwh_5) / 1000, 2, '.', ',');
            $data[0]->bill_1 = number_format($data[0]->kwh_1 * $price, 0, ',', '.');
            $data[0]->bill_2 = number_format($data[0]->kwh_2 * $price, 0, ',', '.');
            $data[0]->bill_3 = number_format($data[0]->kwh_3 * $price, 0, ',', '.');
            $data[0]->bill_4 = number_format($data[0]->kwh_4 * $price, 0, ',', '.');
            $data[0]->bill_5 = number_format($data[0]->kwh_5 * $price, 0, ',', '.');
            $data[0]->bulan = Carbon::create(null, $data[0]->bulan)->monthName;

            return $data;
        }
        for ($i = 0; $i < $length - 1; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);

            // Data inject sebelum Sept 2024
            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->kwh_1 = $data[$i]->kwh_1;
                $data[$i]->kwh_2 = $data[$i]->kwh_2;
                $data[$i]->kwh_3 = $data[$i]->kwh_3;
                $data[$i]->kwh_4 = $data[$i]->kwh_4;
                $data[$i]->kwh_5 = $data[$i]->kwh_5;
            } else {
                $data[$i]->kwh_1 = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
                $data[$i]->kwh_2 = $data[$i]->kwh_2 - $data[$i + 1]->kwh_2;
                $data[$i]->kwh_3 = $data[$i]->kwh_3 - $data[$i + 1]->kwh_3;
                $data[$i]->kwh_4 = $data[$i]->kwh_4 - $data[$i + 1]->kwh_4;
                $data[$i]->kwh_5 = $data[$i]->kwh_5 - $data[$i + 1]->kwh_5;
            }

            $data[$i]->bill_1 = $data[$i]->kwh_1 * $price;
            $data[$i]->bill_2 = $data[$i]->kwh_2 * $price;
            $data[$i]->bill_3 = $data[$i]->kwh_3 * $price;
            $data[$i]->bill_4 = $data[$i]->kwh_4 * $price;
            $data[$i]->bill_5 = $data[$i]->kwh_5 * $price;
            $data[$i]->bulan = Carbon::create(null, $data[$i]->bulan)->monthName;
        }

        return $data;
    }

    public function getAnnualEnergy()
    {
        $data = MdpKwh::select(
            DB::raw('DATE_FORMAT(created_at, "%Y") as tahun '),
            'kwh_1',
            'kwh_2',
            'kwh_3',
            'kwh_4',
            'kwh_5',
            'created_at as timestamp'
        )
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('mdp_kwh')
                    ->groupBy(DB::raw('YEAR(created_at)'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $price = Subdata::latest()->pluck('hargaKwh')->first();;
        $length = count($data);
        $ikeCon = new IkeController();
        $thresholdDate = Carbon::create(2024, 9, 1);


        if ($length == 1) {
            $data[0]->total = number_format(($data[0]->kwh_1) / 1000, 2, '.', ',');
            $data[0]->bill = number_format($data[0]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[0]->bulan = Carbon::create(null, $data[0]->bulan)->monthName;
            $ike = $ikeCon->ikeAnnualClassification($data[0]->total);
            $data[0]->angka_ike = $ike['angka_ike'];
            $data[0]->ike = $ike['ike'];
            $data[0]->color = $ike['color'];

            return $data;
        }

        for ($i = 1; $i < $length; $i++) {
            $currentDate = Carbon::parse($data[$i]->timestamp);
            if ($currentDate->lt($thresholdDate)) {
                $data[$i]->total = $data[$i]->kwh_1;
            } else {
                $data[$i]->total = $data[$i]->kwh_1 - $data[$i + 1]->kwh_1;
            }

            $ike = $ikeCon->ikeAnnualClassification($data[$i]->total);
            $data[$i]->angka_ike = $ike['angka_ike'];
            $data[$i]->ike = $ike['ike'];
            $data[$i]->color = $ike['color'];

            $data[$i]->bill = number_format($data[$i]->total * $price, 0, ',', '.'); // biaya listrik perbulan
            $data[$i]->bulan = Carbon::create(null, $data[$i]->bulan)->monthName;
        }

        return $data;
    }

    public function getEnergyUsageMonitorById($id_kwh)
    {
        /* 
            Today Energy = latest today data - yesterday latest data
        */
        $latestData = MdpKwh::latest()->first();

        $yesterday = Carbon::yesterday();
        $yesterdayData = MdpKwh::whereDate('created_at', $yesterday)->latest()->first();
        if ($yesterdayData == null) {
            $daily = $this->getLimitedDailyEnergy();
            $yesterdayData = $daily[1];
        }
        $todayKwh = $latestData->{"kwh_$id_kwh"}  - $yesterdayData->{"kwh_$id_kwh"};

        $lastTwoMonthsData = $this->getLastTwoMonthsEnergyById($id_kwh);
        $thisMonthKwh = $lastTwoMonthsData[0]->{"kwh_$id_kwh"};
        $thisMonthCost = $lastTwoMonthsData[0]->{"bill_$id_kwh"};
        $lastMonthKwh = $lastTwoMonthsData[1]->{"kwh_$id_kwh"};
        $lastMonthCost = $lastTwoMonthsData[1]->{"bill_$id_kwh"};

        return [
            'todayKwh' => $todayKwh,
            'lastMonthKwh' => $lastMonthKwh,
            'thisMonthKwh' => $thisMonthKwh,
            'lastMonthCost' => $lastMonthCost,
            'thisMonthCost' => $thisMonthCost
        ];
    }

    public function terimaForecast(Request $request)
    {
        // Process the received predictions
        $predictions = $request->all();

        // Store or update the predictions in the database
        foreach ($predictions as $prediction) {
            $existingPrediction = EnergyPredict::where('date', $prediction['date'])->first();
            if ($existingPrediction) {
                // Update the existing prediction
                $existingPrediction->update(['prediction' => $prediction['prediction']]);
            } else {
                // Create a new prediction
                EnergyPredict::create([
                    'date' => $prediction['date'],
                    'prediction' => $prediction['prediction']
                ]);
            }
        }
        // Return a response
        return response()->json(['message' => 'Predictions stored or updated successfully'], 200);
    }

    public function getWeeklyPrediction()
    {
        $data = EnergyPredict::orderBy('id', 'desc')->take(14)->get();

        // Sort ulang agar id kecil berada di atas
        $n = count($data);

        for ($i = 0; $i < $n - 1; $i++) {
            $minIndex = $i;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($data[$j]['id'] < $data[$minIndex]['id']) {
                    $minIndex = $j;
                }
            }
            if ($minIndex != $i) {
                $temp = $data[$i];
                $data[$i] = $data[$minIndex];
                $data[$minIndex] = $temp;
            }
        }

        return $data;
    }
}
