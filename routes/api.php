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
Route::get('/account_info/{account}', [App\Http\Controllers\Api\AccountController::class, 'info'])->name('account.info');
Route::get('/account_tx/{account}', [App\Http\Controllers\Api\AccountController::class, 'tx'])->name('account.tx');
Route::get('/account_lines/{account}', [App\Http\Controllers\Api\AccountController::class, 'lines'])->name('account.lines');

#Analyzer routes
Route::get('/server/queue', [App\Http\Controllers\Api\ServerController::class, 'queue'])->name('server.queue');

#Utilities
Route::get('/currency_rates/{from}/{to}/{amount?}', [App\Http\Controllers\Api\BookController::class, 'currency_rates'])->name('currency_rates');

#Dev routes (not for production)
Route::get('/dev/account/analyze/{account}', [App\Http\Controllers\Api\AccountController::class, 'dev_analyze'])->name('account.dev.dev_analyze');
