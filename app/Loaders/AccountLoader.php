<?php

namespace App\Loaders;

use App\Models\Account;
use App\Statics\XRPL;

class AccountLoader
{

  public $account;
  public $synced = false;

  public function __construct(string $address)
  {
    if(!$address)
      throw new \Exception('Account empty');

    $current_ledger = XRPL::ledger_current();
    //dd($current_ledger);
    //validate $account format
    $account = Account::select([
        'id',
        'account',
        'ledger_first_index',
        'ledger_last_index',
      ])
      ->where('account',$address)
      ->first();

    if(!$account)
    {
      $account = new Account;
      $account->account = $address;
      $account->ledger_first_index = $current_ledger;
    }

    $account->ledger_last_index = $current_ledger;

    $account->save();
    $this->account = $account;
    $this->synced = $account->isHistorySynced();
    return $this;
  }
}
