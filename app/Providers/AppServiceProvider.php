<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      Schema::defaultStringLength(255);

      //date_default_timezone_set('Europe/Zagreb');
      // \Carbon\Carbon::setLocale('hr');

      if($this->app->environment('production')) {
          \URL::forceScheme('https');
      }
    }
}
