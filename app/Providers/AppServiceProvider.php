<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $tickerSymbols = ['^GSPC', '^DJI', '^IXIC', 'GC=F', 'CL=F', 'BTC-USD', 'EURUSD=X'];
            $yahooService = app(\App\Services\YahooFinanceService::class);
            $tickerQuotes = $yahooService->getSparkQuotes($tickerSymbols);
            $view->with('tickerQuotes', $tickerQuotes);
        });
    }
}
