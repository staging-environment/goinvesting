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
        $isPaper = (bool)($user->alpaca_is_paper ?? true);
        
        $tradedSymbols = \App\Models\Trade::where('user_id', $user->id)
            ->where('is_dry_run', $isPaper)
            ->distinct()
            ->pluck('symbol')
            ->toArray();
        sort($tradedSymbols);

        $lastExecution = \App\Models\BotExecution::where('user_id', $user->id)
            ->where('is_paper', $isPaper)
            ->with('trades')
            ->orderBy('started_at', 'desc')
            ->first();
            
        $recentTradesQuery = \App\Models\Trade::where('user_id', $user->id)
            ->where('is_dry_run', $isPaper);

        // Filters
        if ($filterDateFrom = request('filter_date_from')) {
            $recentTradesQuery->whereDate('created_at', '>=', $filterDateFrom);
        }
        if ($filterDateTo = request('filter_date_to')) {
            $recentTradesQuery->whereDate('created_at', '<=', $filterDateTo);
        }
        if ($filterType = request('filter_type')) {
            $recentTradesQuery->where('side', $filterType);
        }
        if ($filterSymbol = request('filter_symbol')) {
            $recentTradesQuery->where('symbol', $filterSymbol);
        }
        if ($filterStatus = request('filter_status')) {
            if ($filterStatus === 'queued') {
                $recentTradesQuery->whereNotIn('status', ['filled', 'rejected', 'canceled', 'cancelled', 'expired']);
            } else {
                $recentTradesQuery->where('status', $filterStatus);
            }
        }

        $recentTrades = $recentTradesQuery->orderBy('created_at', 'asc')
            ->paginate(20)
            ->fragment('recent-trades-section')
            ->withQueryString();

            
        $realTrades = \App\Models\Trade::where('user_id', $user->id)
            ->where('is_dry_run', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $totalRealRealizedPL = (float)\App\Models\Trade::where('user_id', $user->id)
            ->where('is_dry_run', false)
            ->where('side', 'sell')
            ->sum('pnl');

        $recentRealSells = \App\Models\Trade::where('user_id', $user->id)
            ->where('is_dry_run', false)
            ->where('side', 'sell')
            ->whereNotNull('pnl')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $dailySpent = $user->getDailySpent($isPaper);
        $weeklySpent = $user->getWeeklySpent($isPaper);
        $monthlySpent = $user->getMonthlySpent($isPaper);
        $dailyLimit = $isPaper ? $user->daily_spend_limit : $user->live_daily_spend_limit;
        $weeklyLimit = $isPaper ? $user->weekly_spend_limit : $user->live_weekly_spend_limit;
        $monthlyLimit = $isPaper ? $user->monthly_spend_limit : $user->live_monthly_spend_limit;

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
                'tradedSymbols' => $tradedSymbols,
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
            ->where('is_dry_run', (bool)$user->alpaca_is_paper)
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

                // Fetch latest filled buy trade for purchase date
                $lastBuyTrade = \App\Models\Trade::where('user_id', $user->id)
                    ->where('symbol', $symbol)
                    ->where('side', 'buy')
                    ->whereNotIn('status', ['rejected', 'canceled', 'failed'])
                    ->where('is_dry_run', (bool)$user->alpaca_is_paper)
                    ->latest()
                    ->first();
                $purchaseDate = $lastBuyTrade ? $lastBuyTrade->created_at : null;

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
                        : (($pos['cost_basis'] > 0) ? (($pos['market_value'] - $pos['cost_basis']) / $pos['cost_basis']) * 100 : 0.0),
                    'purchase_date' => $purchaseDate,
                    'highest_price' => $lastBuyTrade ? (float)($lastBuyTrade->highest_price ?? $lastBuyTrade->price) : (float)$pos['avg_entry_price'],
                    'dca_level' => $lastBuyTrade ? (int)$lastBuyTrade->dca_level : 0,
                ];
            }
        }

        return view('portfolio', compact(
            'account', 
            'positions', 
            'lastExecution', 
            'recentTrades',
            'realTrades',
            'totalRealRealizedPL',
            'recentRealSells',
            'dailySpent',
            'weeklySpent',
            'monthlySpent',
            'dailyLimit',
            'weeklyLimit',
            'monthlyLimit',
            'statusPaper',
            'statusLive',
            'tradedSymbols'
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
        $activeTab = $request->input('active_tab', $side === 'sell' ? 'positions' : 'portfolio_value');

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

        }

        $pnlValue = null;
        if ($side === 'sell') {
            try {
                $positionInfo = $this->tradingService->getPosition($symbol);
                if ($positionInfo) {
                    $totalQty = (float)($positionInfo['qty'] ?? 0.0);
                    $pendingQty = (float)\App\Models\Trade::where('user_id', $user->id)
                        ->where('is_dry_run', (bool)$user->alpaca_is_paper)
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

        // Cancel any opposing open orders to avoid wash trading errors
        if (method_exists($this->tradingService, 'cancelOrders')) {
            $opposingSide = ($side === 'buy') ? 'sell' : 'buy';
            $this->tradingService->cancelOrders($symbol, $opposingSide);
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
                'broker_order_id' => $result['order']['id'] ?? null,
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
            $friendlyError = $this->translateErrorMessage($result['message'] ?? 'Error desconocido al colocar la orden.');
            return redirect()->back()->withErrors(['error' => $friendlyError])->with('active_tab', $activeTab);
        }
    }

    /**
     * Executes the Trading Bot manually from the UI.
     */
    public function runBot(Request $request)
    {
        $dryRun = $request->has('dry_run');
        $user = auth()->user();
        $mode = $request->input('mode', $user->alpaca_is_paper ? 'paper' : 'live');
        
        $output = '';
        try {
            // Run Artisan command programmatically
            \Illuminate\Support\Facades\Artisan::call('app:trading-bot', [
                '--dry-run' => $dryRun,
                '--user-id' => $user->id,
                '--mode' => $mode
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

    /**
     * Toggles the user's consent to operate the bot with real money in Live mode.
     */
    public function toggleLiveConsent(Request $request)
    {
        $user = auth()->user();
        
        // Toggle the consent state
        $user->alpaca_live_consent = !$user->alpaca_live_consent;
        $user->save();

        $message = $user->alpaca_live_consent 
            ? '¡Consentimiento concedido! El bot de trading automático ahora tiene autorización para operar con dinero real en modo Live.' 
            : 'Consentimiento revocado. Las operaciones del bot de trading automático con dinero real en modo Live han sido desactivadas.';

        return redirect()->back()->with('success', $message)->with('active_tab', 'bot');
    }

    /**
     * Toggles the user's consent to operate the bot in Paper mode.
     */
    public function togglePaperConsent(Request $request)
    {
        $user = auth()->user();
        
        // Toggle the consent state
        $user->alpaca_paper_consent = !$user->alpaca_paper_consent;
        $user->save();

        $message = $user->alpaca_paper_consent 
            ? '¡Consentimiento concedido! El bot de simulación ahora tiene autorización para operar en modo Paper.' 
            : 'Consentimiento revocado. Las operaciones del bot de simulación en modo Paper han sido desactivadas.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Cancels a pending order at the broker and updates the local trade status.
     */
    public function cancelOrder($id)
    {
        $user = auth()->user();
        $trade = \App\Models\Trade::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $activeTab = $trade->side === 'sell' ? 'positions' : 'portfolio_value';

        if ($trade->status === 'canceled' || $trade->status === 'cancelled') {
            return redirect()->back()->with('success', 'La orden ya está cancelada.')->with('active_tab', $activeTab);
        }

        if ($trade->is_dry_run) {
            // Simulated trade, just cancel it locally
            $trade->status = 'canceled';
            $trade->save();
            return redirect()->back()->with('success', 'Orden de simulación cancelada correctamente.')->with('active_tab', $activeTab);
        }

        if (!$trade->broker_order_id) {
            // If there's no broker order ID, we can't cancel it at Alpaca/Lemon, but let's try to cancel it locally
            $trade->status = 'canceled';
            $trade->save();
            return redirect()->back()->with('success', 'La orden no tenía ID del bróker pero ha sido marcada como cancelada en el historial local.')->with('active_tab', $activeTab);
        }

        // Call the API to cancel the order
        $result = $this->tradingService->cancelOrder($trade->broker_order_id);

        if ($result['success']) {
            $trade->status = 'canceled';
            $trade->save();
            return redirect()->back()->with('success', 'Orden cancelada con éxito en el bróker.')->with('active_tab', $activeTab);
        } else {
            // Even if the cancel fails on the API (e.g. order already filled or invalid ID), let's inform the user
            $errorMsg = $this->translateErrorMessage($result['message'] ?? 'Error desconocido al cancelar la orden en el bróker.');
            return redirect()->back()->withErrors(['error' => 'No se pudo cancelar la orden en el bróker: ' . $errorMsg])->with('active_tab', $activeTab);
        }
    }

    /**
     * Translates Alpaca API error messages to user-friendly Spanish.
     */
    protected function translateErrorMessage(string $message): string
    {
        $messageLower = strtolower($message);

        if (str_contains($messageLower, 'potential wash trade')) {
            return 'Se ha detectado una posible operación de autocartera (Wash Trade). Esto ocurre si tienes otra orden pendiente de signo opuesto (por ejemplo, una compra abierta mientras intentas vender). Intenta pulsar el botón de nuevo, ya que el sistema debería haber cancelado la orden conflictiva automáticamente.';
        }

        if (str_contains($messageLower, 'insufficient buying power') || str_contains($messageLower, 'insufficient funds')) {
            return 'Fondos o poder de compra insuficiente en tu cuenta de Alpaca para realizar esta operación.';
        }

        if (str_contains($messageLower, 'unauthorized') || str_contains($messageLower, 'forbidden')) {
            return 'Error de autenticación con Alpaca. Por favor, revisa tus API Keys en tu perfil.';
        }

        if (str_contains($messageLower, 'qty must be positive')) {
            return 'La cantidad debe ser mayor que cero.';
        }

        if (str_contains($messageLower, 'order size is too small')) {
            return 'El tamaño de la orden es demasiado pequeño para este activo en el bróker.';
        }

        if (str_contains($messageLower, 'market is closed')) {
            return 'El mercado está cerrado y esta orden no puede ser procesada actualmente.';
        }

        // Return original message if no translation is available
        return $message;
    }
}
