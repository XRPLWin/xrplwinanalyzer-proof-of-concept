<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Varnish5min
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      $response = $next($request);

      $cache_seconds = 300; //5 min cache for now, until we implement purging

      $response = $response->withHeaders([
        'cache-control' => 'public, s-maxage='.$cache_seconds.', max-age='.$cache_seconds,
        'Expires' =>  date("D, d M Y H:i:s", time()+$cache_seconds).' GMT',
        'Cache-Tags' => 'frontpage test1 test2'
      ]);

      return $response;
    }
}
