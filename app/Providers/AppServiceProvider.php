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
        if (config('app.url') && config('app.url') !== 'http://localhost') {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            if (str_starts_with(config('app.url'), 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        view()->composer('*', function ($view) {
            $tickerSymbols = ['^GSPC', '^DJI', '^IXIC', 'GC=F', 'CL=F', 'BTC-USD', 'EURUSD=X'];
            $yahooService = app(\App\Services\YahooFinanceService::class);
            $tickerQuotes = $yahooService->getSparkQuotes($tickerSymbols);
            $view->with('tickerQuotes', $tickerQuotes);
        });
    }
}
