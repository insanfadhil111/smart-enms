<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PvController;
use App\Http\Controllers\MdpController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\EnmsReportController;
use App\Http\Controllers\MdpSyahrulController;
use App\Http\Controllers\WaterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// MDP
Route::post('add-mdpdata', [MdpController::class, 'addMdpData']);
Route::get('get-mdpdata', [MdpController::class, 'getMdpData']); // Untuk 
Route::get('get-mdpdata/{id}', [MdpController::class, 'getMdpDataById']);
Route::get('get-mdpdata/{id}/{limit}', [MdpController::class, 'getMdpDataByIdLimit']);
Route::post('add-mdpkwh', [MdpController::class, 'addMdpKwh']);
Route::get('get-mdpkwh', [MdpController::class, 'getMdpKwh']);
Route::get('get-mdpkwh/{id}', [MdpController::class, 'getMdpKwhById']);
Route::get('get-mdpkwh/{id}/{limit}', [MdpController::class, 'getMdpKwhByIdLimit']);
Route::get('get-mdp-chart', [MdpController::class, 'getMdpChartData']);

// Device Control
Route::get('get-control', [MdpController::class, 'getControlState']);
Route::post('control/{id}', [MdpController::class, 'switchMdp']);

Route::post('add-syahrul/{id}', [MdpController::class, 'addSyahrul']);

// MPP (Hallway)
Route::post('add-mppdata', [PvController::class, 'addMppData']);
Route::get('get-mppdata/{limit}', [PvController::class, 'getMppData']);

// Goodwe (Rooftop)
Route::post('add-gwdata', [PvController::class, 'addGwData']);
Route::get('get-gwdata/{limit}', [PvController::class, 'getGwData']);

// PV
Route::get('pv-chart-today', [PvController::class, 'getPvChartToday']);
Route::get('pv-chart-week', [PvController::class, 'getPvChartWeek']);
Route::get('pv-chart-month', [PvController::class, 'getPvChartMonth']);
Route::post('pv-chart-filter', [PvController::class, 'getPvChartFilter']);

//Kelompok Rafif
Route::post('add-pv-data', [PvController::class, 'addPvData']);
Route::post('get-pv-data', [PvController::class, 'getPvData']);

// Water
Route::post('add-water', [WaterController::class, 'addWaterData']);
Route::get('get-water/{limit}', [WaterController::class, 'getWaterData']);
Route::get('annual-water', [WaterController::class, 'getAnnualWaterUsage']);
Route::get('monthly-water/{id_dev}', [WaterController::class, 'getMonthlyWater']);
Route::get('this-year-water/{year}', [WaterController::class, 'getThisYearWaterUsage']);

// Other Energy Stuff
Route::get('daily-energy', [EnergyController::class, 'getDailyEnergy']);
Route::get('daily-energy-reversed', [EnergyController::class, 'getDailyEnergyReversed']);
Route::get('monthly-energy', [EnergyController::class, 'getMonthlyEnergy']);
Route::get('annual-energy', [EnergyController::class, 'getAnnualEnergy']);
Route::get('ike-dummy', [EnergyController::class, 'getIkeDummy']);
Route::get('ike-dummy-annual', [EnergyController::class, 'getIkeDummyAnnual']);
Route::post('terima-forecast', [EnergyController::class, 'terimaForecast']);
Route::get('weekly-prediction', [EnergyController::class, 'getWeeklyPrediction']);
Route::get('monthly-report/{year}', [EnmsReportController::class, 'getMonthlyKwhReport']);


// Syahrul (temp)
Route::get('mdp-syahrul', [MdpSyahrulController::class, 'getMdpSyahrul']);
Route::get('util-1-syahrul', [MdpSyahrulController::class, 'getUtil1Syahrul']);
Route::get('util-2-syahrul', [MdpSyahrulController::class, 'getUtil2Syahrul']);
Route::get('now-mdp-syahrul', [MdpSyahrulController::class, 'getNowMdpSyahrul']);
Route::get('now-util-1-syahrul', [MdpSyahrulController::class, 'getNowUtil1Syahrul']);
Route::get('now-util-2-syahrul', [MdpSyahrulController::class, 'getNowUtil2Syahrul']);
Route::get('energy-mdpsyahrul', [MdpSyahrulController::class, 'getEnergyMdp']);
Route::get('energy-util1syahrul', [MdpSyahrulController::class, 'getEnergyUtil1']);
Route::get('energy-util2syahrul', [MdpSyahrulController::class, 'getEnergyUtil2']);


Route::get('debug-func', [EnergyController::class, 'debugFunc']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
