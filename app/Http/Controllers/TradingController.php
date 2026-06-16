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

        $positions = [];
        if ($account && !empty($rawPositions)) {
            // Collect symbols to enrich with current market prices from Yahoo Finance
            $symbols = array_map(fn($pos) => $pos['symbol'], $rawPositions);
            $marketQuotes = $this->yahooService->getSparkQuotes($symbols);

            foreach ($rawPositions as $pos) {
                $symbol = $pos['symbol'];
                $quote = $marketQuotes[$symbol] ?? null;

                $positions[] = [
                    'symbol' => $symbol,
                    'name' => $pos['name'] ?? ($quote['shortName'] ?? $symbol),
                    'qty' => (float)$pos['qty'],
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
            'monthlyLimit'
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

        $user = auth()->user();
        
        // Respect spending limits for manual buys as well
        if ($side === 'buy') {
            // Get approximate cost
            $marketQuotes = $this->yahooService->getSparkQuotes([$symbol]);
            $currentPrice = isset($marketQuotes[$symbol]) ? (float)$marketQuotes[$symbol]['price'] : ($limitPrice ?? 0.0);
            $estimatedCost = $qty * $currentPrice;
            
            if ($user->hasExceededDailyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite diario de gasto (\${$user->daily_spend_limit}, gastado hoy: \${$user->getDailySpent()})."]);
            }
            if ($user->hasExceededWeeklyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite semanal de gasto (\${$user->weekly_spend_limit}, gastado esta semana: \${$user->getWeeklySpent()})."]);
            }
            if ($user->hasExceededMonthlyLimit($estimatedCost)) {
                return redirect()->back()->withErrors(['error' => "La compra manual excede tu límite mensual de gasto (\${$user->monthly_spend_limit}, gastado este mes: \${$user->getMonthlySpent()})."]);
            }
        }

        $result = $this->tradingService->placeOrder($symbol, $qty, $side, $type, $limitPrice);

        if ($result['success']) {
            // Record manual trade
            $marketQuotes = $this->yahooService->getSparkQuotes([$symbol]);
            $price = isset($marketQuotes[$symbol]) ? (float)$marketQuotes[$symbol]['price'] : ($limitPrice ?? 0.0);
            
            \App\Models\Trade::create([
                'user_id' => $user->id,
                'bot_execution_id' => null,
                'symbol' => $symbol,
                'qty' => $qty,
                'price' => $price,
                'side' => $side,
                'status' => 'filled',
                'is_dry_run' => false
            ]);

            $msg = "Orden de " . ($side === 'buy' ? 'Compra' : 'Venta') . " enviada correctamente. ID de Orden: " . $result['order']['id'];
            return redirect()->back()->with('success', $msg);
        } else {
            return redirect()->back()->withErrors(['error' => $result['message']]);
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

        return redirect()->back()->with([
            'success' => 'Ejecución del Bot completada.',
            'bot_output' => $output
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
        $newMode = !$originalMode;

        // Temporarily change and save to test
        $user->alpaca_is_paper = $newMode;
        $user->save();

        $modeText = $newMode ? 'Simulación (Paper)' : 'Real (Live)';
        
        $connectionSuccess = true;
        $connectionMessage = '';

        if ($user->alpaca_key_id && $user->alpaca_secret_key) {
            try {
                $tempService = new \App\Services\AlpacaService(
                    $user->alpaca_key_id, 
                    $user->alpaca_secret_key, 
                    $user->alpaca_account_id, 
                    $newMode
                );
                $accountInfo = $tempService->getAccountInfo();
                if (!$accountInfo) {
                    $connectionSuccess = false;
                    $connectionMessage = "No se pudo conectar con Alpaca en modo {$modeText}. Por favor, verifica que tus credenciales actuales correspondan a este tipo de cuenta.";
                }
            } catch (\Exception $e) {
                $connectionSuccess = false;
                $connectionMessage = 'Error al verificar la conexión: ' . $e->getMessage();
            }
        } else {
            $connectionSuccess = false;
            $connectionMessage = 'No tienes configuradas las credenciales de Alpaca para conectar.';
        }

        if ($connectionSuccess) {
            return redirect()->route('portfolio')->with('success', "Cambiado correctamente a modo {$modeText}. Conexión exitosa.");
        } else {
            // Revert the change since connection failed in the new mode
            $user->alpaca_is_paper = $originalMode;
            $user->save();
            
            return redirect()->route('portfolio')->with('error', $connectionMessage);
        }
    }
}
