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

#Analyzer routes
Route::get('/server/queue', [App\Http\Controllers\Api\ServerController::class, 'queue'])->name('server.queue');

#Utilities
Route::get('/book/liquidity_check/{issuer}/{currency}/{amount}', [App\Http\Controllers\Api\BookController::class, 'liquidity_check'])->name('book.liquidity_check');

#Dev routes (not for production)
Route::get('/dev/account/analyze/{account}', [App\Http\Controllers\Api\AccountController::class, 'dev_analyze'])->name('account.dev.dev_analyze');
