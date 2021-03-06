<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/


Route::get('/', [App\Http\Controllers\Api\InfoController::class, 'info'])->name('info');

#Raw routes
Route::get('/account_info/{account}', [App\Http\Controllers\Api\AccountController::class, 'raw_info'])->name('account.raw_info');
Route::get('/account_tx/{account}', [App\Http\Controllers\Api\AccountController::class, 'raw_tx'])->name('account.raw_tx');
Route::get('/account_lines/{account}', [App\Http\Controllers\Api\AccountController::class, 'raw_lines'])->name('account.raw_lines');

#Account routes
Route::get('/account/info/{account}', [App\Http\Controllers\Api\AccountController::class, 'info'])->middleware('varnish5min')->name('account.info');
Route::get('/account/trustlines/{account}', [App\Http\Controllers\Api\AccountController::class, 'trustlines'])->middleware('varnish5min')->name('account.trustlines');
Route::get('/account/chart/spending/{account}', [App\Http\Controllers\Api\AccountController::class, 'chart_spending'])/*->middleware('varnish5min')*/->name('account.chart.spending');

#Analyzer routes
Route::get('/server/queue', [App\Http\Controllers\Api\ServerController::class, 'queue'])->name('server.queue');

#Utilities
Route::middleware(['varnish5min'])->group(function () {
  Route::get('/currency_rates/{from}/{to}/{amount?}', [App\Http\Controllers\Api\BookController::class, 'currency_rates'])->name('currency_rates');
});


#Dev routes (not for production)
Route::get('/dev/account/analyze/{account}', [App\Http\Controllers\Api\AccountController::class, 'dev_analyze'])->name('account.dev.dev_analyze');
