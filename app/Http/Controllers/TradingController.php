<?php

namespace App\Http\Controllers;

use App\Services\TradingProviderInterface;
use App\Services\YahooFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingController extends Controller
{
    protected TradingProviderInterface $tradingService;
    protected YahooFinanceService $yahooService;

    public function __construct(TradingProviderInterface $tradingService, YahooFinanceService $yahooService)
    {
        $this->tradingService = $tradingService;
        $this->yahooService = $yahooService;
    }

    /**
     * Renders the user's portfolio with open positions and balances.
     */
    /**
     * Renders the user's portfolio with open positions and balances.
     */
    public function portfolio()
    {
        $user = auth()->user();
        
        $lastExecution = \App\Models\BotExecution::where('user_id', $user->id)
            ->with('trades')
            ->orderBy('started_at', 'desc')
            ->first();
            
        $recentTrades = \App\Models\Trade::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();
            
        $dailySpent = $user->getDailySpent();
        $weeklySpent = $user->getWeeklySpent();
        $monthlySpent = $user->getMonthlySpent();
        $dailyLimit = $user->daily_spend_limit;
        $weeklyLimit = $user->weekly_spend_limit;
        $monthlyLimit = $user->monthly_spend_limit;

        // Cache connection status check for both modes to avoid slowing down page loads
        $statusPaper = cache()->remember("alpaca_conn_status_paper_{$user->id}", 600, function() use ($user) {
            if (!$user->alpaca_key_id || !$user->alpaca_secret_key) return 'no_configured';
            try {
                $service = new \App\Services\AlpacaService(
                    $user->alpaca_key_id, 
                    $user->alpaca_secret_key, 
                    $user->alpaca_account_id, 
                    true
                );
                return $service->getAccountInfo() ? 'connected' : 'failed';
            } catch (\Exception $e) {
                return 'failed';
            }
        });

        $statusLive = cache()->remember("alpaca_conn_status_live_{$user->id}", 600, function() use ($user) {
            if (!$user->alpaca_live_key_id || !$user->alpaca_live_secret_key) return 'no_configured';
            try {
                $service = new \App\Services\AlpacaService(
                    $user->alpaca_live_key_id, 
                    $user->alpaca_live_secret_key, 
                    $user->alpaca_live_account_id, 
                    false
                );
                return $service->getAccountInfo() ? 'connected' : 'failed';
            } catch (\Exception $e) {
                return 'failed';
            }
        });

        if (!$this->tradingService->isConfigured()) {
            return view('portfolio', [
                'error' => 'No has configurado tus credenciales personales de Alpaca o el proveedor actual no está configurado. Por favor, añade tus credenciales en tu Perfil.',
                'lastExecution' => $lastExecution,
                'recentTrades' => $recentTrades,
                'dailySpent' => $dailySpent,
                'weeklySpent' => $weeklySpent,
                'monthlySpent' => $monthlySpent,
                'dailyLimit' => $dailyLimit,
                'weeklyLimit' => $weeklyLimit,
                'monthlyLimit' => $monthlyLimit,
                'statusPaper' => $statusPaper,
                'statusLive' => $statusLive,
            ]);
        }

        $account = $this->tradingService->getAccountInfo();
        
        if ($account && !$user->wizard_completed) {
            $recommendedSymbols = ['AAPL', 'AMZN', 'MSFT'];
            $marketQuotes = $this->yahooService->getSparkQuotes($recommendedSymbols);
            
            $recommendedAssets = [];
            foreach ($recommendedSymbols as $sym) {
                $quote = $marketQuotes[$sym] ?? null;
                $recommendedAssets[] = [
                    'symbol' => $sym,
                    'name' => $sym === 'AAPL' ? 'Apple' : ($sym === 'AMZN' ? 'Amazon' : 'Microsoft'),
                    'price' => $quote ? (float)$quote['price'] : 0.0,
                    'changePercent' => $quote ? (float)$quote['changePercent'] : 0.0
                ];
            }
            
            return view('portfolio-wizard', compact('account', 'recommendedAssets'));
        }

        $rawPositions = $this->tradingService->getPositions();
        
        // Translate Alpaca crypto symbols (e.g. BTC/USD) to Yahoo format (BTC-USD)
        if (!empty($rawPositions)) {
            $rawPositions = array_map(function($pos) {
                if (isset($pos['symbol'])) {
                    $pos['symbol'] = str_replace('/', '-', $pos['symbol']);
                }
                return $pos;
            }, $rawPositions);
        }

        $pendingSells = \App\Models\Trade::where('user_id', $user->id)
            ->where('side', 'sell')
            ->whereNotIn('status', ['filled', 'rejected', 'canceled', 'expired'])
            ->get()
            ->groupBy('symbol')
            ->map(fn($trades) => $trades->sum('qty'));

        $positions = [];
        if ($account && !empty($rawPositions)) {
            // Collect symbols to enrich with current market prices from Yahoo Finance
            $symbols = array_map(fn($pos) => $pos['symbol'], $rawPositions);
            $marketQuotes = $this->yahooService->getSparkQuotes($symbols);

            foreach ($rawPositions as $pos) {
                $symbol = $pos['symbol'];
                $quote = $marketQuotes[$symbol] ?? null;
                $pendingQty = (float)($pendingSells[$symbol] ?? 0.0);

                $positions[] = [
                    'symbol' => $symbol,
                    'name' => $pos['name'] ?? ($quote['shortName'] ?? $symbol),
                    'qty' => (float)$pos['qty'],
                    'pending_qty' => $pendingQty,
                    'available_qty' => max(0.0, (float)$pos['qty'] - $pendingQty),
                    'avg_entry_price' => (float)$pos['avg_entry_price'],
                    'current_price' => $quote ? (float)$quote['price'] : (float)($pos['current_price'] ?? $pos['avg_entry_price']),
                    'cost_basis' => (float)$pos['cost_basis'],
                    'market_value' => $quote ? ((float)$quote['price'] * (float)$pos['qty']) : (float)$pos['market_value'],
                    'unrealized_pl' => $quote 
                        ? (((float)$quote['price'] * (float)$pos['qty']) - (float)$pos['cost_basis']) 
                        : (float)($pos['unrealized_pl'] ?? 0.0),
                    'unrealized_plpc' => $quote 
                        ? ((((float)$quote['price'] * (float)$pos['qty']) - (float)$pos['cost_basis']) / (float)$pos['cost_basis']) * 100 
                        : (($pos['cost_basis'] > 0) ? (($pos['market_value'] - $pos['cost_basis']) / $pos['cost_basis']) * 100 : 0.0)
                ];
            }
        }

        return view('portfolio', compact(
            'account', 
            'positions', 
            'lastExecution', 
            'recentTrades',
            'dailySpent',
            'weeklySpent',
            'monthlySpent',
            'dailyLimit',
            'weeklyLimit',
            'monthlyLimit',
            'statusPaper',
            'statusLive'
        ));
    }

    /**
     * Executes a buy or sell order via active Trading API.
     */
    public function executeOrder(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'qty' => 'required|numeric|min:0.0001',
            'side' => 'required|string|in:buy,sell',
            'type' => 'required|string|in:market,limit',
            'limit_price' => 'nullable|required_if:type,limit|numeric|min:0.01'
        ]);

        $symbol = strtoupper($request->input('symbol'));
        $qty = (float)$request->input('qty');
        $side = $request->input('side');
        $type = $request->input('type');
        $limitPrice = $request->has('limit_price') ? (float)$request->input('limit_price') : null;
        $activeTab = $request->input('active_tab', $side === 'sell' ? 'positions' : 'overview');

        $user = auth()->user();
        
        // Respect spending limits for manual buys as well
        if ($side === 'buy') {
            // Get approximate cost
            $marketQuotes = $this->yahooService->getSparkQuotes([$symbol]);
            $currentPrice = isset($marketQuotes[$symbol]) ? (float)$marketQuotes[$symbol]['price'] : ($limitPrice ?? 0.0);
            $estimatedCost = $qty * $currentPrice;
            
            if ($user->hasExceededDailyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite diario de gasto (\${$user->daily_spend_limit}, gastado hoy: \${$user->getDailySpent()})."])->with('active_tab', $activeTab);
            }
            if ($user->hasExceededWeeklyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite semanal de gasto (\${$user->weekly_spend_limit}, gastado esta semana: \${$user->getWeeklySpent()})."])->with('active_tab', $activeTab);
            }
            if ($user->hasExceededMonthlyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite mensual de gasto (\${$user->monthly_spend_limit}, gastado este mes: \${$user->getMonthlySpent()})."])->with('active_tab', $activeTab);
            }
        }

        $pnlValue = null;
        if ($side === 'sell') {
            try {
                $positionInfo = $this->tradingService->getPosition($symbol);
                if ($positionInfo) {
                    $totalQty = (float)($positionInfo['qty'] ?? 0.0);
                    $pendingQty = (float)\App\Models\Trade::where('user_id', $user->id)
                        ->where('symbol', $symbol)
                        ->where('side', 'sell')
                        ->whereNotIn('status', ['filled', 'rejected', 'canceled', 'expired'])
                        ->sum('qty');
                    $availableQty = max(0.0, $totalQty - $pendingQty);

                    if ($qty > $availableQty) {
                        return redirect()->back()->withErrors(['error' => "No puedes vender {$qty} unidades de {$symbol}. Ya tienes {$pendingQty} en cola de venta, dejándote solo {$availableQty} disponibles para vender."])->with('active_tab', $activeTab);
                    }

                    $avgEntry = (float)($positionInfo['avg_entry_price'] ?? 0.0);
                    if ($avgEntry > 0) {
                        // Let's resolve the execution price to calculate PnL
                        $marketQuotes = $this->yahooService->getSparkQuotes([$symbol]);
                        $price = isset($marketQuotes[$symbol]) ? (float)$marketQuotes[$symbol]['price'] : ($limitPrice ?? 0.0);
                        $pnlValue = ($price - $avgEntry) * $qty;
                    }
                }
            } catch (\Exception $e) {
                // Ignore position lookup errors
            }
        }

        $result = $this->tradingService->placeOrder($symbol, $qty, $side, $type, $limitPrice);

        if ($result['success']) {
            // Record manual trade
            $marketQuotes = $this->yahooService->getSparkQuotes([$symbol]);
            $price = isset($marketQuotes[$symbol]) ? (float)$marketQuotes[$symbol]['price'] : ($limitPrice ?? 0.0);
            $orderStatus = $result['order']['status'] ?? 'filled';
            
            \App\Models\Trade::create([
                'user_id' => $user->id,
                'bot_execution_id' => null,
                'symbol' => $symbol,
                'qty' => $qty,
                'price' => $price,
                'side' => $side,
                'status' => $orderStatus,
                'is_dry_run' => (bool)$user->alpaca_is_paper,
                'pnl' => $pnlValue
            ]);

            $isMarketOpen = $this->tradingService->isMarketOpen();
            $msg = "Orden de " . ($side === 'buy' ? 'Compra' : 'Venta') . " enviada correctamente. ID de Orden: " . $result['order']['id'];
            if (!$isMarketOpen) {
                $msg .= "<br><br>⚠️ <strong>Nota de Mercado Cerrado:</strong> El mercado de valores de EE.UU. está cerrado actualmente (abre de Lunes a Viernes de 15:30 a 22:00 hora de España). Tu orden ha quedado encolada de forma segura y se ejecutará automáticamente cuando abra el mercado. Tus acciones correspondientes han quedado retenidas/bloqueadas temporalmente en el broker para evitar operaciones duplicadas.";
            }

            return redirect()->back()->with('success', $msg)->with('active_tab', $activeTab);
        } else {
            return redirect()->back()->withErrors(['error' => $result['message']])->with('active_tab', $activeTab);
        }
    }

    /**
     * Executes the Trading Bot manually from the UI.
     */
    public function runBot(Request $request)
    {
        $dryRun = $request->has('dry_run');
        $user = auth()->user();
        
        $output = '';
        try {
            // Run Artisan command programmatically
            \Illuminate\Support\Facades\Artisan::call('app:trading-bot', [
                '--dry-run' => $dryRun,
                '--user-id' => $user->id
            ]);
            $output = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Exception $e) {
            $output = "Excepción al ejecutar el bot: " . $e->getMessage();
        }

        $activeTab = $request->input('active_tab', 'bot');

        return redirect()->back()->with([
            'success' => 'Ejecución del Bot completada.',
            'bot_output' => $output,
            'active_tab' => $activeTab
        ]);
    }

    /**
     * Completes the onboarding wizard.
     */
    public function completeWizard(Request $request)
    {
        $user = auth()->user();
        $user->wizard_completed = true;
        $user->save();

        return redirect()->route('portfolio')->with('success', '¡Asistente completado! Bienvenido a tu portafolio en vivo.');
    }

    /**
     * Toggles the user's Alpaca mode between Paper (Simulation) and Live (Real).
     */
    public function togglePaper(Request $request)
    {
        $user = auth()->user();
        
        $originalMode = (bool)$user->alpaca_is_paper;
        
        if ($request->has('mode')) {
            $newMode = $request->input('mode') === 'paper';
            if ($newMode === $originalMode) {
                return redirect()->back(); // Already in requested mode
            }
        } else {
            $newMode = !$originalMode;
        }

        // Temporarily change and save to test
        $user->alpaca_is_paper = $newMode;
        $user->save();

        $modeText = $newMode ? 'Simulación (Paper)' : 'Real (Live)';
        
        $connectionSuccess = true;
        $connectionMessage = '';

        // FIX: Resolve the keys for the NEW mode we are testing, NOT always the paper mode
        $keyId = $newMode ? ($user->alpaca_key_id ?? '') : ($user->alpaca_live_key_id ?? '');
        $secretKey = $newMode ? ($user->alpaca_secret_key ?? '') : ($user->alpaca_live_secret_key ?? '');
        $accountId = $newMode ? ($user->alpaca_account_id ?? '') : ($user->alpaca_live_account_id ?? '');

        if ($keyId && $secretKey) {
            try {
                $tempService = new \App\Services\AlpacaService(
                    $keyId, 
                    $secretKey, 
                    $accountId, 
                    $newMode
                );
                $accountInfo = $tempService->getAccountInfo();
                if (!$accountInfo) {
                    $connectionSuccess = false;
                    if (!$newMode) {
                        $connectionMessage = "No se pudo conectar con Alpaca en modo Real (Live).<br><br>" .
                            "<strong class='text-slate-100'>¿Tu cuenta es nueva?</strong> Alpaca suele tardar entre 24 y 72 horas hábiles en verificar y activar nuevas cuentas reales. Hasta que tu cuenta no esté completamente aprobada y activa por su equipo, tus llaves de API reales devolverán un error de conexión no autorizada.<br><br>" .
                            "Puedes comprobar el estado de tu aprobación iniciando sesión en tu <a href='https://app.alpaca.markets' target='_blank' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>Panel de Control de Alpaca</a>, contactar directamente con su soporte en <a href='mailto:support@alpaca.markets' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>support@alpaca.markets</a> o visitar el <a href='https://support.alpaca.markets' target='_blank' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>Centro de Ayuda de Alpaca</a>.";
                    } else {
                        $connectionMessage = "No se pudo conectar con Alpaca en modo Simulación (Paper). Por favor, verifica que tus credenciales actuales de simulación correspondan a este tipo de cuenta.";
                    }
                }
            } catch (\Exception $e) {
                $connectionSuccess = false;
                $connectionMessage = 'Error al verificar la conexión: ' . $e->getMessage();
            }
        } else {
            $connectionSuccess = false;
            $connectionMessage = 'No tienes configuradas las credenciales de Alpaca para conectar en modo ' . $modeText . '.';
        }

        // Clear cache keys so that the status is re-evaluated
        cache()->forget("alpaca_conn_status_paper_{$user->id}");
        cache()->forget("alpaca_conn_status_live_{$user->id}");

        if ($connectionSuccess) {
            return redirect()->back()->with('success', "Cambiado correctamente a modo {$modeText}. Conexión exitosa.");
        } else {
            // Revert the change since connection failed in the new mode
            $user->alpaca_is_paper = $originalMode;
            $user->save();
            
            $originalModeText = $originalMode ? 'Simulación (Paper)' : 'Real (Live)';
            $fullMessage = "No se pudo cambiar al modo <strong>{$modeText}</strong> debido a un fallo de conexión. <br><strong>Hemos mantenido tu modo activo en {$originalModeText}</strong>.<br><br>" . $connectionMessage;
            
            return redirect()->back()->with('error', $fullMessage);
        }
    }
}
