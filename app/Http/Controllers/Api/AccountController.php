<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
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
      dd('Sync queued, please check back later, TODO respond generic json here');
    }

    $info = XRPL::account_info($account);
    return response()->json($info);
  }

  public function tx(string $account)
  {
    $txs = XRPL::account_tx($account);
    return response()->json($txs);
  }

  public function lines(string $account)
  {
    $txs = XRPL::account_lines($account);
    return response()->json($txs);
  }


  public function dev_analyze(string $account)
  {
    $acct = new AccountLoader($account,false);
    if(!$acct->exists)
      dd('Does not exist locally');
    if(!$acct->synced)
      dd('Not synced');

    StaticAccount::analyzeData($acct->account);
    //dd($acct);
  }

}
