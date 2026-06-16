<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingProviderInterface;
use App\Services\YahooFinanceService;
use Illuminate\Support\Facades\Log;

class TradingBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trading-bot {--dry-run : Only simulate actions, do not place orders} {--user-id= : Run for a specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated trading bot executing orders on active Trading API based on Yahoo Finance quotes';

    protected TradingProviderInterface $tradingService;
    protected YahooFinanceService $yahooService;
    protected float $maxInvestmentLimit;
    protected float $orderSize; // Buy amount worth of shares per trade

    // Strategy Parameters
    protected float $buyThresholdPercent = -1.5; // Buy if daily change is <= -1.5%
    protected float $takeProfitPercent = 2.0;    // Sell if gain >= 2.0%
    protected float $stopLossPercent = -3.0;      // Sell if loss <= -3.0%

    // Assets to monitor: Yahoo Finance Symbol => Alpaca/Lemon Symbol
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

    protected string $outputBuffer = '';

    public function __construct(TradingProviderInterface $tradingService, YahooFinanceService $yahooService)
    {
        parent::__construct();
        $this->tradingService = $tradingService;
        $this->yahooService = $yahooService;
        
        $this->maxInvestmentLimit = (float)env('BOT_MAX_INVESTMENT_LIMIT', 5000.0);
        $this->orderSize = (float)env('BOT_ORDER_SIZE', 500.0);
    }

    protected function logLine(string $message, string $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $this->outputBuffer .= "[{$timestamp}] [" . strtoupper($level) . "] {$message}\n";
        
        switch ($level) {
            case 'warn':
                $this->warn($message);
                break;
            case 'error':
                $this->error($message);
                break;
            case 'success':
                $this->line("<fg=green>{$message}</>");
                break;
            default:
                $this->info($message);
                break;
        }
    }

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user-id');
        
        // Resolve user
        $user = null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                $this->error("Error: El usuario con ID {$userId} no existe.");
                return Command::FAILURE;
            }
        } else {
            $user = \App\Models\User::first();
            if (!$user) {
                $this->error("Error: No hay ningún usuario en la base de datos para asociar la ejecución.");
                return Command::FAILURE;
            }
        }

        // Configure Alpaca service with user credentials if available
        if ($user->alpaca_key_id && $user->alpaca_secret_key) {
            $this->tradingService = new \App\Services\AlpacaService(
                $user->alpaca_key_id,
                $user->alpaca_secret_key,
                $user->alpaca_account_id,
                $user->alpaca_is_paper
            );
        }

        // Load bot strategy and limit parameters from the user's settings, falling back to env/defaults
        $this->buyThresholdPercent = (float)($user->bot_buy_threshold ?? -1.5);
        $this->takeProfitPercent = (float)($user->bot_take_profit ?? 2.0);
        $this->stopLossPercent = (float)($user->bot_stop_loss ?? -3.0);
        $this->orderSize = (float)($user->bot_order_size ?? env('BOT_ORDER_SIZE', 500.0));
        $this->maxInvestmentLimit = (float)($user->bot_max_investment ?? env('BOT_MAX_INVESTMENT_LIMIT', 500000.0));


        // Create BotExecution record
        $execution = \App\Models\BotExecution::create([
            'user_id' => $user->id,
            'started_at' => \Carbon\Carbon::now(),
            'status' => 'running',
            'is_dry_run' => $dryRun,
            'output' => ''
        ]);

        $this->logLine("=== Iniciando Bot de Trading Automático ===");
        $this->logLine("Usuario: {$user->name} (ID: {$user->id})");
        Log::info("Bot de Trading: Iniciando ejecución para usuario ID {$user->id}.");

        if ($dryRun) {
            $this->logLine("MODO DE SIMULACIÓN (DRY-RUN) ACTIVO. No se ejecutarán operaciones reales.", 'warn');
        }

        if (!$this->tradingService->isConfigured()) {
            $errorMsg = "Error: El bróker de trading actual no está configurado.";
            $this->logLine($errorMsg, 'error');
            $execution->update([
                'finished_at' => \Carbon\Carbon::now(),
                'status' => 'failed',
                'output' => $this->outputBuffer
            ]);
            return Command::FAILURE;
        }

        // 1. Fetch Account Info
        $account = $this->tradingService->getAccountInfo();
        if (!$account) {
            $errorMsg = "Error: No se pudo obtener la información de la cuenta.";
            $this->logLine($errorMsg, 'error');
            $execution->update([
                'finished_at' => \Carbon\Carbon::now(),
                'status' => 'failed',
                'output' => $this->outputBuffer
            ]);
            return Command::FAILURE;
        }

        $cash = (float)$account['cash'];
        $portfolioValue = (float)$account['portfolio_value'];
        $this->logLine("Balance de cuenta (Efectivo): \${$cash}");
        $this->logLine("Valor total del Portafolio: \${$portfolioValue}");

        // 2. Fetch Open Positions
        $rawPositions = $this->tradingService->getPositions();
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

        $this->logLine("Total invertido actualmente: \${$totalInvested} (Límite máximo de inversión: \${$this->maxInvestmentLimit})");
        $this->logLine("Límite diario del usuario: " . ($user->daily_spend_limit ? "\${$user->daily_spend_limit}" : "Sin límite") . " | Gastado hoy: \${$user->getDailySpent()}");
        $this->logLine("Límite semanal del usuario: " . ($user->weekly_spend_limit ? "\${$user->weekly_spend_limit}" : "Sin límite") . " | Gastado esta semana: \${$user->getWeeklySpent()}");

        // 3. Fetch Market Data for monitored assets
        $yahooSymbols = array_keys($this->monitoredAssets);
        $marketQuotes = $this->yahooService->getSparkQuotes($yahooSymbols);
        if (empty($marketQuotes)) {
            $errorMsg = "Error: No se pudieron obtener cotizaciones de Yahoo Finance.";
            $this->logLine($errorMsg, 'error');
            $execution->update([
                'finished_at' => \Carbon\Carbon::now(),
                'status' => 'failed',
                'output' => $this->outputBuffer
            ]);
            return Command::FAILURE;
        }

        // 4. Process Monitored Assets
        foreach ($this->monitoredAssets as $yahooSymbol => $tradingSymbol) {
            if (!isset($marketQuotes[$yahooSymbol])) {
                $this->logLine("No hay cotizaciones disponibles para {$yahooSymbol}.", 'warn');
                continue;
            }

            $quote = $marketQuotes[$yahooSymbol];
            $currentPrice = (float)$quote['price'];
            $dailyChangePercent = (float)$quote['changePercent'];

            $this->logLine("Analizando {$tradingSymbol} | Precio: \${$currentPrice} | Cambio Hoy: " . number_format($dailyChangePercent, 2) . "%");

            // Check if we hold a position
            if (isset($positions[$tradingSymbol])) {
                // Sell logic (Take Profit / Stop Loss)
                $pos = $positions[$tradingSymbol];
                $avgEntry = $pos['avg_entry_price'];
                $returnPercent = (($currentPrice - $avgEntry) / $avgEntry) * 100;

                $this->logLine("-> Tienes posición: {$pos['qty']} unidades. Rendimiento: " . number_format($returnPercent, 2) . "%");

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
                    $this->logLine("-> ALERTA DE VENTA para {$tradingSymbol}: {$reason}", 'warn');
                    Log::info("Bot de Trading: Venta recomendada para {$tradingSymbol} ({$reason}). Rendimiento: {$returnPercent}%");

                    if ($dryRun) {
                        $this->logLine("-> [Simulación] Orden de venta enviada para {$pos['qty']} unidades de {$tradingSymbol}");
                        
                        \App\Models\Trade::create([
                            'user_id' => $user->id,
                            'bot_execution_id' => $execution->id,
                            'symbol' => $tradingSymbol,
                            'qty' => $pos['qty'],
                            'price' => $currentPrice,
                            'side' => 'sell',
                            'status' => 'filled',
                            'is_dry_run' => true
                        ]);
                    } else {
                        $res = $this->tradingService->placeOrder($tradingSymbol, $pos['qty'], 'sell', 'market');
                        if ($res['success']) {
                            $this->logLine("-> [EJECUTADO] Venta completada. Orden ID: " . $res['order']['id'], 'success');
                            Log::info("Bot de Trading: Venta ejecutada de {$pos['qty']} unidades de {$tradingSymbol} con éxito.");
                            
                            \App\Models\Trade::create([
                                'user_id' => $user->id,
                                'bot_execution_id' => $execution->id,
                                'symbol' => $tradingSymbol,
                                'qty' => $pos['qty'],
                                'price' => $currentPrice,
                                'side' => 'sell',
                                'status' => 'filled',
                                'is_dry_run' => false
                            ]);
                        } else {
                            $this->logLine("-> [FALLÓ] Error al vender {$tradingSymbol}: " . $res['message'], 'error');
                        }
                    }
                }
            } else {
                // Buy logic (Negative Daily Change and under budget limits)
                if ($dailyChangePercent <= $this->buyThresholdPercent) {
                    $this->logLine("-> ALERTA DE COMPRA para {$tradingSymbol}: Cambio diario de " . number_format($dailyChangePercent, 2) . "% por debajo del umbral de {$this->buyThresholdPercent}%");

                    // Check budget limits
                    if ($totalInvested + $this->orderSize > $this->maxInvestmentLimit) {
                        $this->logLine("-> Compra cancelada: Supera el límite máximo de inversión de \${$this->maxInvestmentLimit}", 'warn');
                        continue;
                    }

                    if ($cash < $this->orderSize) {
                        $this->logLine("-> Compra cancelada: Efectivo disponible (\${$cash}) insuficiente para la orden de \${$this->orderSize}", 'warn');
                        continue;
                    }

                    // Check user spending limits
                    if ($user->hasExceededDailyLimit($this->orderSize)) {
                        $this->logLine("-> Compra cancelada: Supera el límite diario de gasto del usuario (\${$user->daily_spend_limit}, gastado hoy: \${$user->getDailySpent()})", 'warn');
                        continue;
                    }

                    if ($user->hasExceededWeeklyLimit($this->orderSize)) {
                        $this->logLine("-> Compra cancelada: Supera el límite semanal de gasto del usuario (\${$user->weekly_spend_limit}, gastado esta semana: \${$user->getWeeklySpent()})", 'warn');
                        continue;
                    }

                    if ($user->hasExceededMonthlyLimit($this->orderSize)) {
                        $this->logLine("-> Compra cancelada: Supera el límite mensual de gasto del usuario (\${$user->monthly_spend_limit}, gastado este mes: \${$user->getMonthlySpent()})", 'warn');
                        continue;
                    }

                    $qtyToBuy = round($this->orderSize / $currentPrice, 4);
                    if ($qtyToBuy <= 0) {
                        $this->logLine("-> Compra cancelada: Cantidad a comprar es 0 debido al alto precio del activo.", 'warn');
                        continue;
                    }

                    $this->logLine("-> Iniciando compra de {$qtyToBuy} unidades de {$tradingSymbol} (\${$this->orderSize})", 'warn');
                    Log::info("Bot de Trading: Intento de compra para {$tradingSymbol} (precio: \${$currentPrice}, cantidad: {$qtyToBuy})");

                    if ($dryRun) {
                        $this->logLine("-> [Simulación] Orden de compra enviada para {$qtyToBuy} unidades de {$tradingSymbol}");
                        
                        \App\Models\Trade::create([
                            'user_id' => $user->id,
                            'bot_execution_id' => $execution->id,
                            'symbol' => $tradingSymbol,
                            'qty' => $qtyToBuy,
                            'price' => $currentPrice,
                            'side' => 'buy',
                            'status' => 'filled',
                            'is_dry_run' => true
                        ]);
                        // Update values for consecutive orders in this run
                        $totalInvested += $this->orderSize;
                        $cash -= $this->orderSize;
                    } else {
                        $res = $this->tradingService->placeOrder($tradingSymbol, $qtyToBuy, 'buy', 'market');
                        if ($res['success']) {
                            $this->logLine("-> [EJECUTADO] Compra completada. Orden ID: " . $res['order']['id'], 'success');
                            Log::info("Bot de Trading: Compra ejecutada de {$qtyToBuy} unidades de {$tradingSymbol} con éxito.");
                            
                            \App\Models\Trade::create([
                                'user_id' => $user->id,
                                'bot_execution_id' => $execution->id,
                                'symbol' => $tradingSymbol,
                                'qty' => $qtyToBuy,
                                'price' => $currentPrice,
                                'side' => 'buy',
                                'status' => 'filled',
                                'is_dry_run' => false
                            ]);
                            // Update values for consecutive orders in this run
                            $totalInvested += $this->orderSize;
                            $cash -= $this->orderSize;
                        } else {
                            $this->logLine("-> [FALLÓ] Error al comprar {$tradingSymbol}: " . $res['message'], 'error');
                        }
                    }
                }
            }
        }

        $this->logLine("=== Ejecución del Bot de Trading Finalizada ===");
        Log::info("Bot de Trading: Ejecución finalizada correctamente para usuario ID {$user->id}.");

        $execution->update([
            'finished_at' => \Carbon\Carbon::now(),
            'status' => 'success',
            'output' => $this->outputBuffer
        ]);

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
