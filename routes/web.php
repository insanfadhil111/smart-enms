<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PvController;
use App\Http\Controllers\MdpController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\WaterController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\LightsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\EnmsReportController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\IkeController;
use App\Http\Controllers\SubdataController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountLogController;

Route::get('/', function () {
	return redirect('/dashboard');
})->middleware('auth');

Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');


Route::group(['middleware' => 'auth'], function () {
	Route::get('/dashboard', [HomeController::class, 'index'])->name('home')->middleware('auth');

	Route::get('/energy-monitor', [EnergyController::class, 'monitor'])->name('energy-monitor');
	Route::get('/energy-control', [EnergyController::class, 'showControl'])->name('energy-control');
	Route::get('/energy-stats', [EnergyController::class, 'stats'])->name('energy-stats');

	Route::get('/nre', [PvController::class, 'nreIndex'])->name('nreIndex');

	// Route::get('/standar-ike', [EnergyController::class, 'standarIke'])->name('standar-ike');
	Route::get('/standar-ike', [IkeController::class, 'index'])->name('standar-ike');

	Route::get('/water-monitor', [WaterController::class, 'monitor'])->name('water-monitor');

	Route::get('/enms-report', [EnmsReportController::class, 'index'])->name('enms-report');

	// Settings
	Route::get('subdatas', [SubdataController::class, 'index'])->name('subdatas.index');
	Route::put('subdatas', [SubdataController::class, 'update'])->name('subdatas.update');
	Route::get('subdatas/reset', [SubdataController::class, 'reset'])->name('subdatas.reset');

	Route::get('/security-camera', [SecurityController::class, 'index'])->name('security-camera');
	Route::get('/security-doorlock', [SecurityController::class, 'doorlock'])->name('security-doorlock');

	// Device Control
	Route::get('switch-mdp/{id}', [MdpController::class, 'switchMdp'])->name('switch-mdp');
	Route::get('switch-light/{id}', [EnvironmentController::class, 'switchLight'])->name('switch-light');

	Route::get('/envi-sense', [EnvironmentController::class, 'monitor'])->name('envi-sense');
	Route::get('/envi-lights', [LightsController::class, 'showControl'])->name('envi-lights');

	Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
	Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
	Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
	Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static');
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static');
	Route::get('/pages/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');

	//Building
	Route::get('/building', [BuildingController::class, 'index'])->name('building.index');
	Route::post('/building/store', [BuildingController::class, 'store'])->name('building.store');
	Route::resource('building', BuildingController::class);

	//New Building Auto
	Route::get('/new-dashboard-{path}', [BuildingController::class, 'newDashboard'])->name('building.newDashboard');
	Route::post('/buildings', [BuildingController::class, 'store'])->name('building.store');
	// Route::get('/building/{id}', [BuildingController::class, 'show'])->name('building.show')->middleware('auth');
	Route::post('/buildings/create-dashboard', [BuildingController::class, 'createDashboard'])->name('buildings.createDashboard');
	Route::get('/buildings/{building}/new-dashboard', [BuildingController::class, 'newDashboard'])->name('buildings.newDashboard');
	
	//Logo Gambar Ganti Sesuai Input New Building

	//Account controller
	Route::get('/account', [AccountController::class, 'index'])->name('account');
	Route::get('/account', [AccountController::class, 'index'])->name('account.index');
	Route::get('/users/create', [AccountController::class, 'create'])->name('users.create');
	Route::post('/users', [AccountController::class, 'store'])->name('users.store');
	Route::get('/account/{id}/edit', [AccountController::class, 'edit'])->name('account.edit');
	Route::put('/account/{id}', [AccountController::class, 'update'])->name('account.update');
	Route::delete('/account/{id}', [AccountController::class, 'destroy'])->name('account.destroy');

	//Account Log controller
	Route::get('/account-log', [AccountLogController::class, 'index'])->name('account.log');
	Route::get('/account-log', [AccountLogController::class, 'index'])->name('account-log.index');

	//dummy
	Route::post('/api/test-forecast', function (Request $request) {
		Log::info('Received data:', $request->all());
		return response()->json(['message' => 'Data received successfully']);
	});

	//Kelompok Rafif
	Route::post('/pv-data', 'PvController@addPvData');
	Route::get('/pv-data', 'PvController@getPvData');

});

Route::fallback(function () {
	return view('pages/404');
})->name('404');
