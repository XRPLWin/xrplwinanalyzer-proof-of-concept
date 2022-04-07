<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
use App\Models\Account;
use App\Loaders\AccountLoader;
use App\Models\AggregatDailyPayment;
use Carbon\CarbonPeriod;

class AccountController extends Controller
{

  public function raw_info(string $account)
  {
    $info = XRPL::account_info($account);
    return response()->json($info);
  }

  public function raw_tx(string $account)
  {
    $txs = XRPL::account_tx($account);
    return response()->json($txs);
  }

  public function raw_lines(string $account)
  {
    $txs = XRPL::account_lines($account);
    return response()->json($txs);
  }

  ###############

  public function info(string $account)
  {
    $r = [
      'synced' => true,   // true|false
      'type' => 'normal', // normal|issuer|exchange
    ];
    $acct = new AccountLoader($account);
    //dd($acct);
    if(!$acct->synced)
    {
      $acct->account->sync();
      $r['synced'] = false;
    }

    $info = XRPL::account_info($account);

    $account_data = $info['result']['account_data'];
    $r['Balance'] = $account_data['Balance'];
    $r['Flags'] = $account_data['Flags'];
    if(isset($account_data['RegularKey']))
      $r['RegularKey'] = $account_data['RegularKey'];
    if(isset($account_data['Domain']))
      $r['Domain'] = $account_data['Domain'];
    if(isset($account_data['EmailHash']))
      $r['EmailHash'] = $account_data['EmailHash'];


    //get if this account is issuer or not by checking obligations
    $gateway_balances = XRPL::gateway_balances($account);

    if(isset($gateway_balances['result']['obligations']) && !empty($gateway_balances['result']['obligations']))
      $r['type'] = 'issuer';

    return response()->json($r);
  }

  public function trustlines(string $account)
  {
    $txs = XRPL::account_lines($account);
    $lines = $txs['result']['lines'];

    $trustlines = [];

    foreach($lines as $line) {
      $trustlines[] = [
        'account' => $line['account'],
        'currency' => $line['currency'],
        'symbol' => xrp_currency_to_symbol($line['currency']),
        'balance' => $line['balance'],
        'limit' => $line['limit']
      ];
    }

    return response()->json($trustlines);
  }

  /**
  * Chart data spending in XRP for account
  */
  public function chart_spending(string $account)
  {
    $acct = new AccountLoader($account);
    if(!$acct->synced)
      return response()->json([]);

    $end = now();
    $start = now()->addDays(-330);

  //  dd($acct->account);
    $aggr = AggregatDailyPayment::select('amount','balance','date')
      ->where('account_id',$acct->account->id)
      ->whereBetween('date',[$start,$end])
      ->orderBy('date','asc')
      ->get();

    //Normalize ranges
    $range = new CarbonPeriod($start,'1 day',$end);
    //dd($range);

    $r = [];
    $balance = 0;
    //get starting balance
    $startingBalance = AggregatDailyPayment::select('balance')
      ->where('account_id',$acct->account->id)
      ->where('date', '<', $start)
      ->orderBy('date','desc')
      ->first();
    if($startingBalance)
      $balance = $startingBalance->balance;
    foreach($range as $day)
    {
      //dd($day);
      $data = $aggr->where('date',$day->startOfDay())->first();
      if(!$data) {
        $r[] = [
          $day->timestamp,
          0,
          $balance
        ];
      }
      else {
        $r[] = [
          $day->timestamp,
          $data->amount,
          $data->balance,
        ];
        $balance = $data->balance;
      }
    }

    return response()->json($r);
  }


  public function dev_analyze(string $account)
  {
    $acct = new AccountLoader($account,false);
    if(!$acct->exists)
      dd('Does not exist locally');
    if(!$acct->synced)
      dd('Not synced');

    StaticAccount::analyzeData($acct->account);
    dd($acct);
  }

}
