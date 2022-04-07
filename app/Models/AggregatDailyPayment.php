<?php

namespace App\Models;


class AggregatDailyPayment extends Transaction
{
  protected $table = 'aggregate_daily_payments';

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'date' => 'date',
  ];

}
