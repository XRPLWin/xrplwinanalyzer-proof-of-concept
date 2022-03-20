<?php

namespace App\Loaders;

use App\Models\Account;
use App\Statics\XRPL;

class AccountLoader
{

  public $account;
  public $synced = false;
  public $exists = false;

  public function __construct(string $address, $createIfEmpty = true)
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
        'is_history_synced'
      ])
      ->where('account',$address)
      ->first();


    if(!$account)
    {
      if(!$createIfEmpty)
        return $this;

      $account = new Account;
      $account->account = $address;
      $account->ledger_first_index = $current_ledger;
    }

    $account->ledger_last_index = $current_ledger;

    $account->save();
    $this->account = $account;
    $this->synced = $account->is_history_synced;
    $this->exists = true;
    return $this;
  }
}
