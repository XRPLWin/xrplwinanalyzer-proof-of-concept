<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Statics\XRPL;
use App\Models\Account;
use App\Loaders\AccountLoader;

class AccountController extends Controller
{

  public function info(string $account)
  {

    $acct = new AccountLoader($account);
    //dd($acct);
    if(!$acct->synced)
    {
      $acct->account->sync();
      dd('synced respond now');
    }









    $info = XRPL::account_info($account);
    return response()->json($info);
  }

  public function tx(string $account)
  {
    $txs = XRPL::account_tx($account);
    return response()->json($txs);
  }



}
