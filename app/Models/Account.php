<?php

namespace App\Models;
use App\Statics\XRPL;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\QueueArtisanCommand;
use Illuminate\Support\Facades\DB;

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

  public function sync(bool $recursive = true)
  {
    //check if already synced
    $check = DB::table('jobs')->where('qtype','account')->where('qtype_data',$this->account)->count();
    if($check)
      return;

    $job = QueueArtisanCommand::dispatch(
      'xrpl:accountsync',
      ['address' => $this->account, '--recursiveaccountqueue' => $recursive ],
      'account',
      $this->account
    )->onQueue('default');
  }

  # Relationships

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function tx_payments_where_source()
  {
      return $this->hasMany(TransactionPayment::class ,'source_account_id');
  }

  public function tx_payments_where_destination()
  {
      return $this->hasMany(TransactionPayment::class ,'destination_account_id');
  }

  public function tx_offers()
  {
      return $this->hasMany(TransactionOffer::class ,'account_id');
  }



}
