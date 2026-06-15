<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlpacaService;
use App\Services\YahooFinanceService;
use Illuminate\Support\Facades\Log;

class TradingBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trading-bot {--dry-run : Only simulate actions, do not place orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated trading bot executing orders on Alpaca Sandbox based on Yahoo Finance quotes';

    protected AlpacaService $alpacaService;
    protected YahooFinanceService $yahooService;

    // Investment constraints (EUR/USD conversion simplified as USD is account currency)
    // 10000 EUR is roughly 11000 USD. We will use $11000 USD as the limit.
    protected float $maxInvestmentLimit = 11000.0;
    protected float $orderSize = 500.0; // Buy $500 worth of shares per trade

    // Strategy Parameters
    protected float $buyThresholdPercent = -1.5; // Buy if daily change is <= -1.5%
    protected float $takeProfitPercent = 2.0;    // Sell if gain >= 2.0%
    protected float $stopLossPercent = -3.0;      // Sell if loss <= -3.0%

    // Assets to monitor: Yahoo Finance Symbol => Alpaca Symbol
    protected array $monitoredAssets = [
        'AAPL' => 'AAPL',
        'MSFT' => 'MSFT',
        'GOOGL' => 'GOOGL',
        'AMZN' => 'AMZN',
        'TSLA' => 'TSLA',
        'NVDA' => 'NVDA',
        'META' => 'META',
        'BTC-USD' => 'BTC/USD',
        'ETH-USD' => 'ETH/USD',
    ];

    public function __construct(AlpacaService $alpacaService, YahooFinanceService $yahooService)
    {
        parent::__construct();
        $this->alpacaService = $alpacaService;
        $this->yahooService = $yahooService;
    }

    public function handle()
    {
        $this->info("=== Iniciando Bot de Trading Automático ===");
        Log::info("Bot de Trading: Iniciando ejecución.");

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn("MODO DE SIMULACIÓN (DRY-RUN) ACTIVO. No se ejecutarán operaciones reales.");
        }

        if (!$this->alpacaService->isConfigured()) {
            $this->error("Error: Alpaca no está configurado en el archivo .env.");
            return Command::FAILURE;
        }

        // 1. Fetch Alpaca Account Info
        $account = $this->alpacaService->getAccountInfo();
        if (!$account) {
            $this->error("Error: No se pudo obtener la información de la cuenta de Alpaca.");
            return Command::FAILURE;
        }

        $cash = (float)$account['cash'];
        $portfolioValue = (float)$account['portfolio_value'];
        $this->info("Balance de cuenta (Efectivo): \${$cash}");
        $this->info("Valor total del Portafolio: \${$portfolioValue}");

        // 2. Fetch Open Positions
        $rawPositions = $this->alpacaService->getPositions();
        $positions = [];
        $totalInvested = 0.0;

        foreach ($rawPositions as $pos) {
            $symbol = strtoupper($pos['symbol']);
            // Normalize Alpaca symbol (e.g. BTCUSD -> BTC/USD)
            if ($symbol === 'BTCUSD') $symbol = 'BTC/USD';
            if ($symbol === 'ETHUSD') $symbol = 'ETH/USD';

            $costBasis = (float)$pos['cost_basis'];
            $positions[$symbol] = [
                'qty' => (float)$pos['qty'],
                'avg_entry_price' => (float)$pos['avg_entry_price'],
                'cost_basis' => $costBasis,
                'market_value' => (float)$pos['market_value'],
            ];
            $totalInvested += $costBasis;
        }

        $this->info("Total invertido actualmente: \${$totalInvested} (Límite máximo: \${$this->maxInvestmentLimit})");

        // 3. Fetch Market Data for monitored assets
        $yahooSymbols = array_keys($this->monitoredAssets);
        $marketQuotes = $this->yahooService->getSparkQuotes($yahooSymbols);
        if (empty($marketQuotes)) {
            $this->error("Error: No se pudieron obtener cotizaciones de Yahoo Finance.");
            return Command::FAILURE;
        }

        // 4. Process Monitored Assets
        foreach ($this->monitoredAssets as $yahooSymbol => $alpacaSymbol) {
            if (!isset($marketQuotes[$yahooSymbol])) {
                $this->warn("No hay cotizaciones disponibles para {$yahooSymbol}.");
                continue;
            }

            $quote = $marketQuotes[$yahooSymbol];
            $currentPrice = (float)$quote['price'];
            $dailyChangePercent = (float)$quote['changePercent'];

            $this->info("Analizando {$alpacaSymbol} | Precio: \${$currentPrice} | Cambio Hoy: " . number_format($dailyChangePercent, 2) . "%");

            // Check if we hold a position
            if (isset($positions[$alpacaSymbol])) {
                // Sell logic (Take Profit / Stop Loss)
                $pos = $positions[$alpacaSymbol];
                $avgEntry = $pos['avg_entry_price'];
                $returnPercent = (($currentPrice - $avgEntry) / $avgEntry) * 100;

                $this->info("-> Tienes posición: {$pos['qty']} unidades. Rendimiento: " . number_format($returnPercent, 2) . "%");

                $shouldSell = false;
                $reason = "";

                if ($returnPercent >= $this->takeProfitPercent) {
                    $shouldSell = true;
                    $reason = "Take Profit alcanzado (+{$this->takeProfitPercent}%)";
                } elseif ($returnPercent <= $this->stopLossPercent) {
                    $shouldSell = true;
                    $reason = "Stop Loss alcanzado ({$this->stopLossPercent}%)";
                }

                if ($shouldSell) {
                    $this->warn("-> ALERTA DE VENTA para {$alpacaSymbol}: {$reason}");
                    Log::info("Bot de Trading: Venta recomendada para {$alpacaSymbol} ({$reason}). Rendimiento: {$returnPercent}%");

                    if ($dryRun) {
                        $this->info("-> [Simulación] Orden de venta enviada para {$pos['qty']} unidades de {$alpacaSymbol}");
                    } else {
                        $res = $this->alpacaService->placeOrder($alpacaSymbol, $pos['qty'], 'sell', 'market');
                        if ($res['success']) {
                            $this->success("-> [EJECUTADO] Venta completada. Orden ID: " . $res['order']['id']);
                            Log::info("Bot de Trading: Venta ejecutada de {$pos['qty']} unidades de {$alpacaSymbol} con éxito.");
                        } else {
                            $this->error("-> [FALLÓ] Error al vender {$alpacaSymbol}: " . $res['message']);
                        }
                    }
                }
            } else {
                // Buy logic (Negative Daily Change and under budget limits)
                if ($dailyChangePercent <= $this->buyThresholdPercent) {
                    $this->info("-> ALERTA DE COMPRA para {$alpacaSymbol}: Cambio diario de " . number_format($dailyChangePercent, 2) . "% por debajo del umbral de {$this->buyThresholdPercent}%");

                    // Check budget limits
                    if ($totalInvested + $this->orderSize > $this->maxInvestmentLimit) {
                        $this->warn("-> Compra cancelada: Supera el límite máximo de inversión de \${$this->maxInvestmentLimit}");
                        continue;
                    }

                    if ($cash < $this->orderSize) {
                        $this->warn("-> Compra cancelada: Efectivo disponible (\${$cash}) insuficiente para la orden de \${$this->orderSize}");
                        continue;
                    }

                    $qtyToBuy = round($this->orderSize / $currentPrice, 4);
                    if ($qtyToBuy <= 0) {
                        $this->warn("-> Compra cancelada: Cantidad a comprar es 0 debido al alto precio del activo.");
                        continue;
                    }

                    $this->warn("-> Iniciando compra de {$qtyToBuy} unidades de {$alpacaSymbol} (\${$this->orderSize})");
                    Log::info("Bot de Trading: Intento de compra para {$alpacaSymbol} (precio: \${$currentPrice}, cantidad: {$qtyToBuy})");

                    if ($dryRun) {
                        $this->info("-> [Simulación] Orden de compra enviada para {$qtyToBuy} unidades de {$alpacaSymbol}");
                    } else {
                        $res = $this->alpacaService->placeOrder($alpacaSymbol, $qtyToBuy, 'buy', 'market');
                        if ($res['success']) {
                            $this->success("-> [EJECUTADO] Compra completada. Orden ID: " . $res['order']['id']);
                            Log::info("Bot de Trading: Compra ejecutada de {$qtyToBuy} unidades de {$alpacaSymbol} con éxito.");
                            // Update values for consecutive orders in this run
                            $totalInvested += $this->orderSize;
                            $cash -= $this->orderSize;
                        } else {
                            $this->error("-> [FALLÓ] Error al comprar {$alpacaSymbol}: " . $res['message']);
                        }
                    }
                }
            }
        }

        $this->info("=== Ejecución del Bot de Trading Finalizada ===");
        Log::info("Bot de Trading: Ejecución finalizada correctamente.");
        return Command::SUCCESS;
    }

    /**
     * Success output color.
     */
    protected function success(string $message)
    {
        $this->line("<fg=green>{$message}</>");
    }
}
