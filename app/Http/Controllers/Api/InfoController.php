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
      'description' => 'XRPLWin Analyzer (https://xrpl.win)'
    ]);
  }
}
