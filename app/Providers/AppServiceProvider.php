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
        $this->app->bind(\App\Services\TradingProviderInterface::class, function ($app) {
            $provider = env('TRADING_PROVIDER', 'alpaca');
            if ($provider === 'lemon') {
                return $app->make(\App\Services\LemonMarketsService::class);
            }
            
            $user = auth()->user();
            if ($user) {
                $isPaper = (bool)($user->alpaca_is_paper ?? true);
                $keyId = $isPaper ? ($user->alpaca_key_id ?? '') : ($user->alpaca_live_key_id ?? '');
                $secretKey = $isPaper ? ($user->alpaca_secret_key ?? '') : ($user->alpaca_live_secret_key ?? '');
                $accountId = $isPaper ? $user->alpaca_account_id : $user->alpaca_live_account_id;

                return new \App\Services\AlpacaService(
                    $keyId,
                    $secretKey,
                    $accountId,
                    $isPaper
                );
            }
            
            return new \App\Services\AlpacaService(
                config('services.alpaca.key_id'),
                config('services.alpaca.secret_key'),
                config('services.alpaca.account_id'),
                config('services.alpaca.is_paper', true)
            );
        });
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
            $tickerMetadata = [
                '^GSPC' => [
                    'name' => 'S&P 500',
                    'desc' => 'Índice de las 500 empresas líderes de EE.UU. Mide la salud general de la economía estadounidense.'
                ],
                '^DJI' => [
                    'name' => 'Dow Jones',
                    'desc' => 'Índice bursátil que agrupa a las 30 corporaciones industriales más grandes de EE.UU.'
                ],
                '^IXIC' => [
                    'name' => 'Nasdaq',
                    'desc' => 'Índice bursátil tecnológico centrado en empresas de software, internet y hardware.'
                ],
                'GC=F' => [
                    'name' => 'Oro',
                    'desc' => 'Futuros del Oro. Activo de refugio tradicional para proteger capital frente a la inflación.'
                ],
                'CL=F' => [
                    'name' => 'Petróleo Brent',
                    'desc' => 'Futuros de Petróleo Crudo Brent. Referencia para la fijación de precios de energía global.'
                ],
                'BTC-USD' => [
                    'name' => 'Bitcoin',
                    'desc' => 'Criptomoneda líder, utilizada como reserva de valor digital descentralizada.'
                ],
                'EURUSD=X' => [
                    'name' => 'Euro / Dólar',
                    'desc' => 'Tipo de cambio del Euro frente al Dólar. Indica cuántos dólares vale un euro.'
                ]
            ];
            $yahooService = app(\App\Services\YahooFinanceService::class);
            $tickerQuotes = $yahooService->getSparkQuotes($tickerSymbols);
            $view->with('tickerQuotes', $tickerQuotes)->with('tickerMetadata', $tickerMetadata);
        });
    }
}

