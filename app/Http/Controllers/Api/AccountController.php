<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{

  public function account()
  {
    return response()->json(['test' => 123]);
  }





    /*public function ott_token_array(string $token)
    {

      $client = new \GuzzleHttp\Client();
      $url = config('xumm.endpoint').'/xapp/ott/'.$token;

      # TESTING https://xumm.readme.io/reference/re-fetch-ott-data

      $sha = \sha1(\strtoupper(config('xumm.app_api_key').config('xumm.app_api_secret').'EE05F68B-0D0E-4C97-BC39-744494B4DECF'));
      $url .= '/'.$sha;
      # TESTING END
      $response = $client->request('GET', $url, [
        'headers' => [
          'Accept' => 'application/json',
          'X-API-Key' => config('xumm.app_api_key'),
          'X-API-Secret' => config('xumm.app_api_secret'),
        ],
      ]);

      $ret = \json_decode($response->getBody(),true);
      return $ret;
    }

    public function ott_token(Request $request)
    {
      $token = $request->input('token');
      abort(404);

      return response()->json($token);
    }*/
}
