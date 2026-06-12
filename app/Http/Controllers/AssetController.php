<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\YahooFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    protected YahooFinanceService $yahooService;

    public function __construct(YahooFinanceService $yahooService)
    {
        $this->yahooService = $yahooService;
    }

    /**
     * Dashboard page showing major markets and user watchlist.
     */
    public function index()
    {
        $indices = ['^GSPC', '^DJI', '^IXIC', '^FTSE', '^GDAXI', '^N225', '^IBEX'];
        $stocks = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'TSLA', 'NVDA', 'META'];
        $forex = ['EURUSD=X', 'GBPUSD=X', 'USDJPY=X', 'AUDUSD=X', 'USDCAD=X', 'EURGBP=X'];
        $crypto = ['BTC-USD', 'ETH-USD', 'SOL-USD', 'BNB-USD', 'ADA-USD', 'XRP-USD'];
        $commodities = ['GC=F', 'CL=F', 'SI=F', 'NG=F', 'BZ=F'];

        // Gather all default symbols to fetch in one multi-spark call
        $allDefaultSymbols = array_merge($indices, $stocks, $forex, $crypto, $commodities);
        $sparkQuotes = $this->yahooService->getSparkQuotes($allDefaultSymbols);

        // Map quotes back to their categories
        $data = [
            'indices' => $this->filterQuotes($indices, $sparkQuotes),
            'stocks' => $this->filterQuotes($stocks, $sparkQuotes),
            'forex' => $this->filterQuotes($forex, $sparkQuotes),
            'crypto' => $this->filterQuotes($crypto, $sparkQuotes),
            'commodities' => $this->filterQuotes($commodities, $sparkQuotes),
            'watchlist' => []
        ];

        // If authenticated, load user watchlist quotes and Alpaca summary
        $data['alpacaAccount'] = null;
        if (Auth::check()) {
            $watchlistSymbols = Auth::user()->watchlists()->pluck('symbol')->toArray();
            if (!empty($watchlistSymbols)) {
                $watchlistQuotes = $this->yahooService->getSparkQuotes($watchlistSymbols);
                $data['watchlist'] = $this->filterQuotes($watchlistSymbols, $watchlistQuotes);
            }

            $alpacaService = app(\App\Services\AlpacaService::class);
            if ($alpacaService->isConfigured()) {
                $data['alpacaAccount'] = $alpacaService->getAccountInfo();
            }
        }

        return view('welcome', $data);
    }

    /**
     * Detail page for a specific asset.
     */
    public function show(string $symbol)
    {
        $symbol = strtoupper($symbol);
        
        // Fetch 1d chart by default (which contains latest day stats as well)
        $assetData = $this->yahooService->getChartData($symbol, '1d', '5m');
        
        if (!$assetData) {
            abort(404, "Asset not found");
        }

        $isWatched = false;
        if (Auth::check()) {
            $isWatched = Auth::user()->watchlists()->where('symbol', $symbol)->exists();
        }

        return view('assets.show', compact('assetData', 'isWatched'));
    }

    /**
     * JSON proxy for historical chart data (used via AJAX).
     */
    public function getChart(Request $request, string $symbol)
    {
        $range = $request->query('range', '1d');
        $interval = $request->query('interval', '5m');

        // Validate ranges to prevent malicious inputs
        $validRanges = ['1d', '5d', '1mo', '3mo', '6mo', '1y', '2y', '5y', 'max'];
        if (!in_array($range, $validRanges)) {
            $range = '1d';
        }

        // Adjust interval automatically for best display if not specified
        if (!$request->has('interval')) {
            switch ($range) {
                case '1d': $interval = '5m'; break;
                case '5d': $interval = '30m'; break;
                case '1mo': $interval = '1d'; break;
                case '3mo': $interval = '1d'; break;
                case '6mo': $interval = '1d'; break;
                case '1y': $interval = '1wk'; break;
                case '5y': $interval = '1mo'; break;
                case 'max': $interval = '1mo'; break;
                default: $interval = '1d';
            }
        }

        $chartData = $this->yahooService->getChartData($symbol, $range, $interval);
        
        if (!$chartData) {
            return response()->json(['error' => 'Unable to fetch chart data'], 404);
        }

        return response()->json($chartData);
    }

    /**
     * Autocomplete search JSON endpoint.
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->yahooService->search($query);
        return response()->json($results);
    }

    /**
     * JSON endpoint to fetch quotes for all categories and watchlist.
     */
    public function getQuotes()
    {
        $indices = ['^GSPC', '^DJI', '^IXIC', '^FTSE', '^GDAXI', '^N225', '^IBEX'];
        $stocks = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'TSLA', 'NVDA', 'META'];
        $forex = ['EURUSD=X', 'GBPUSD=X', 'USDJPY=X', 'AUDUSD=X', 'USDCAD=X', 'EURGBP=X'];
        $crypto = ['BTC-USD', 'ETH-USD', 'SOL-USD', 'BNB-USD', 'ADA-USD', 'XRP-USD'];
        $commodities = ['GC=F', 'CL=F', 'SI=F', 'NG=F', 'BZ=F'];

        $allDefaultSymbols = array_merge($indices, $stocks, $forex, $crypto, $commodities);
        $sparkQuotes = $this->yahooService->getSparkQuotes($allDefaultSymbols);

        $data = [
            'indices' => $this->filterQuotes($indices, $sparkQuotes),
            'stocks' => $this->filterQuotes($stocks, $sparkQuotes),
            'forex' => $this->filterQuotes($forex, $sparkQuotes),
            'crypto' => $this->filterQuotes($crypto, $sparkQuotes),
            'commodities' => $this->filterQuotes($commodities, $sparkQuotes),
            'watchlist' => []
        ];

        if (Auth::check()) {
            $watchlistSymbols = Auth::user()->watchlists()->pluck('symbol')->toArray();
            if (!empty($watchlistSymbols)) {
                $watchlistQuotes = $this->yahooService->getSparkQuotes($watchlistSymbols);
                $data['watchlist'] = $this->filterQuotes($watchlistSymbols, $watchlistQuotes);
            }
        }

        return response()->json($data);
    }

    /**
     * Helper to order/filter quotes according to input list.
     */
    private function filterQuotes(array $symbols, array $quotes): array
    {
        $list = [];
        foreach ($symbols as $sym) {
            if (isset($quotes[$sym])) {
                $list[] = $quotes[$sym];
            }
        }
        return $list;
    }
}
