<?php

namespace App\Http\Controllers;

use App\Services\AlpacaService;
use App\Services\YahooFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingController extends Controller
{
    protected AlpacaService $alpacaService;
    protected YahooFinanceService $yahooService;

    public function __construct(AlpacaService $alpacaService, YahooFinanceService $yahooService)
    {
        $this->alpacaService = $alpacaService;
        $this->yahooService = $yahooService;
    }

    /**
     * Renders the user's Alpaca portfolio with open positions and balances.
     */
    public function portfolio()
    {
        if (!$this->alpacaService->isConfigured()) {
            return view('portfolio', [
                'error' => 'La API de Alpaca no está configurada. Por favor, añade tus credenciales en el archivo .env.'
            ]);
        }

        $account = $this->alpacaService->getAccountInfo();
        $rawPositions = $this->alpacaService->getPositions();

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
                    'name' => $quote['shortName'] ?? $symbol,
                    'qty' => (float)$pos['qty'],
                    'avg_entry_price' => (float)$pos['avg_entry_price'],
                    'current_price' => $quote ? (float)$quote['price'] : (float)$pos['current_price'],
                    'cost_basis' => (float)$pos['cost_basis'],
                    'market_value' => $quote ? ((float)$quote['price'] * (float)$pos['qty']) : (float)$pos['market_value'],
                    'unrealized_pl' => $quote 
                        ? (((float)$quote['price'] * (float)$pos['qty']) - (float)$pos['cost_basis']) 
                        : (float)$pos['unrealized_pl'],
                    'unrealized_plpc' => $quote 
                        ? ((((float)$quote['price'] * (float)$pos['qty']) - (float)$pos['cost_basis']) / (float)$pos['cost_basis']) * 100 
                        : (float)$pos['unrealized_intraday_plpc'] * 100
                ];
            }
        }

        return view('portfolio', compact('account', 'positions'));
    }

    /**
     * Executes a buy or sell order via Alpaca API.
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

        $result = $this->alpacaService->placeOrder($symbol, $qty, $side, $type, $limitPrice);

        if ($result['success']) {
            $msg = "Orden de " . ($side === 'buy' ? 'Compra' : 'Venta') . " enviada correctamente. ID de Orden: " . $result['order']['id'];
            return redirect()->back()->with('success', $msg);
        } else {
            return redirect()->back()->withErrors(['error' => $result['message']]);
        }
    }
}
