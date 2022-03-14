<?php

namespace App\Models;
use App\Statics\XRPL;
use App\Jobs\XrplAccountSyncJob;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
  public $timestamps = false;

  public function isHistorySynced() : bool
  {
    return !($this->ledger_first_index > config('xrpl.genesis_ledger'));
  }

  public function sync()
  {
    XrplAccountSyncJob::dispatch($this);
    //$txs = XRPL::account_tx($this->account);
    //dd($txs);
  }
}
