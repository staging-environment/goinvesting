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
    public function portfolio()
    {
        if (!$this->tradingService->isConfigured()) {
            return view('portfolio', [
                'error' => 'El proveedor de trading actual no está configurado. Por favor, añade tus credenciales en el archivo .env.'
            ]);
        }

        $account = $this->tradingService->getAccountInfo();
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

        return view('portfolio', compact('account', 'positions'));
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

        $result = $this->tradingService->placeOrder($symbol, $qty, $side, $type, $limitPrice);

        if ($result['success']) {
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
        
        $output = '';
        try {
            // Run Artisan command programmatically
            \Illuminate\Support\Facades\Artisan::call('app:trading-bot', [
                '--dry-run' => $dryRun
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
}
