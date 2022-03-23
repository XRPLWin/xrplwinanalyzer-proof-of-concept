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
        //TODO
        [
          'action' => 'Get account info',
          'route' => '/account_info/{account}',
          'method' => 'GET'
        ]
      ]
    ]);
  }
}
