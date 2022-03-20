<?php

namespace App\Statics;

#use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
#use Illuminate\Support\Facades\Cache;
use App\Models\Account as AccountModel;
use App\Models\Topaccount;

class Account
{

  public static function GetOrCreate($address, $current_ledger)
  {
    $check = AccountModel::where('account',$address)->count();
    if($check)
    {
      $account = AccountModel::select([
        'id',
        'account',
        'ledger_first_index',
        'ledger_last_index',
      ])->where('account',$address)->first();
      return $account;
    }

    $account = new AccountModel;
    $account->account = $address;
    $account->ledger_first_index = $current_ledger;
    $account->ledger_last_index = $current_ledger;
    $account->save();
    return $account;
  }

  /**
  * Analyze synced data.
  * @see https://xrpl.org/issuing-and-operational-addresses.html
  TEST: http://xlanalyzer.test/dev/account/analyze/rM1oqKtfh1zgjdAgbFmaRm3btfGBX25xVo
  **/
  public static function analyzeData(AccountModel $account) : bool
  {
    if(!$account->is_history_synced)
      return false; //not synced fully

    # 1. Detect hot wallets
    #    To detect hot wallets we will examine transactions and detect large amount of token flow from issuer account.

    //TODO
    //dd($account->tx_payments_where_source->first(),$account->tx_payments_where_destination->first());

    // Iterate over all transactions detecting where this account sent token currency
    $account->tx_payments_where_source()
      ->where('issuer_account_id', $account->id)
      ->orderBy('time_at','asc')
      ->chunk(200, function ($payments) {

        foreach ($payments as $payment) {
          //This is payment in token currency from issuer to external account.
          dd($payment);
        }
      });

    # 2. Aggregate payments and store them in DB.
    #

    //TODO


    return true;
  }

}
