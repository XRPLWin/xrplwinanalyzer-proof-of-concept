<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfoController extends Controller
{

  public function info()
  {
    return response()->json([
      'version' => config('xwin.version'),
      'description' => 'XRPLWin Analyzer',
      'license' => 'ISC License',
      'copyright' => 'Copyright (c) 2022, XRPLWin (https://xrpl.win)',
      'documentation' => 'TODO',
      //'release-notes' => 'TODO',
      'endpoints' => [
        [
          'action' => 'Get account info',
          'route' => '/account/info/{account}',
          'method' => 'GET'
        ],
        [
          'action' => 'Trustline info for account',
          'route' => '/account/trustlines/{account}',
          'method' => 'GET'
        ],
        [
          'action' => 'Chart data spending in XRP for account',
          'route' => '/account/chart/spending/{account}',
          'method' => 'GET'
        ],
        [
          'action' => 'Get queue info',
          'route' => '/server/queue',
          'method' => 'GET'
        ],
        [
          'action' => 'Get currency exchange rate',
          'route' => '/currency_rates/{from}/{to}/{amount?}',
          'method' => 'GET',
          'example' => config('app.url').'/currency_rates/USD+rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq/XRP',
        ]
      ]
    ]);
  }
}
