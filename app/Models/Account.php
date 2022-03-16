<?php

namespace App\Models;
use App\Statics\XRPL;
use App\Jobs\XrplAccountSyncJob;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
  public $timestamps = false;

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
      'is_history_synced' => 'boolean',
  ];

  public function isHistorySynced() : bool
  {
    return $this->is_history_synced;
    //return !($this->ledger_first_index > config('xrpl.genesis_ledger'));
  }

  public function sync()
  {
    XrplAccountSyncJob::dispatch($this);
    //$txs = XRPL::account_tx($this->account);
    //dd($txs);
  }
}
