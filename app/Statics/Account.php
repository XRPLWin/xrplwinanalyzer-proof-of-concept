<?php

namespace App\Statics;

#use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
#use Illuminate\Support\Facades\Cache;
use App\Models\Account as AccountModel;

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

  

}
