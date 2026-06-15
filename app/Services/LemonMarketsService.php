<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LemonMarketsService implements TradingProviderInterface
{
    protected string $apiKey;
    protected ?string $accountId;
    protected bool $isPaper;
    protected string $baseUrl;

    // Static mapping of common US stocks monitored by the bot to their ISINs
    protected array $symbolToIsinMap = [
        'AAPL'  => 'US0378331005',
        'MSFT'  => 'US5949181045',
        'GOOGL' => 'US02079K3059',
        'AMZN'  => 'US0231351067',
        'TSLA'  => 'US88160R1014',
        'NVDA'  => 'US67066G1040',
        'META'  => 'US30303M1027',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.lemon.key') ?? '';
        $this->accountId = config('services.lemon.account_id');
        $this->isPaper = (bool)config('services.lemon.is_paper', true);

        // Base URL based on environment
        $this->baseUrl = $this->isPaper
            ? 'https://sandbox.api.lemon.markets/v1'
            : 'https://api.lemon.markets/v1';
    }

    /**
     * Check if the API credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->accountId);
    }

    /**
     * Helper to get request headers.
     */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // Mandatory data privacy headers for Lemon.markets Brokerage API
            'LMG-Data-Privacy-Access-Principal' => 'backend-nobody',
            'LMG-Data-Privacy-Access-Justification' => 'trading_operations',
        ];
    }

    /**
     * Resolve symbol/ticker to ISIN using static map or API search.
     */
    public function resolveSymbolToIsin(string $symbol): ?string
    {
        $symbol = strtoupper(trim($symbol));
        // Normalize common crypto tickers if they contain dashes
        $normalizedSymbol = str_replace('-USD', '', $symbol);
        $normalizedSymbol = str_replace('/USD', '', $normalizedSymbol);

        if (isset($this->symbolToIsinMap[$normalizedSymbol])) {
            return $this->symbolToIsinMap[$normalizedSymbol];
        }

        // Call the /instruments search endpoint
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/instruments", [
                    'search' => $normalizedSymbol
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['results']) && isset($data['results'][0]['isin'])) {
                    return $data['results'][0]['isin'];
                }
            }
        } catch (\Exception $e) {
            Log::error("LemonMarkets Symbol Resolution Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch account details.
     */
    public function getAccountInfo(): ?array
    {
        if (!$this->isConfigured()) return null;

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/accounts/{$this->accountId}");

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['results'] ?? $data;

                // Map Lemon.markets fields to the unifed format required by the app.
                // Lemon.markets response typically has:
                // - cash_to_invest or balance
                $cash = (float)($result['cash_to_invest'] ?? $result['balance'] ?? 0.0);
                $portfolioValue = (float)($result['balance'] ?? $cash); // Fallback to cash if total balance isn't present

                return [
                    'cash' => $cash,
                    'portfolio_value' => $portfolioValue,
                    'account_number' => $result['account_id'] ?? $this->accountId,
                    'currency' => $result['currency'] ?? 'EUR',
                ];
            } else {
                Log::error("LemonMarkets Account Info Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("LemonMarkets Account Info Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch all open positions.
     */
    public function getPositions(): array
    {
        if (!$this->isConfigured()) return [];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/accounts/{$this->accountId}/positions");

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['results'] ?? [];

                $positions = [];
                foreach ($results as $pos) {
                    // Normalize position attributes. Lemon.markets returns:
                    // - isin
                    // - isin_title
                    // - quantity
                    // - buy_price_avg
                    // - estimated_value_total
                    $qty = (float)($pos['quantity'] ?? 0.0);
                    $avgEntry = (float)($pos['buy_price_avg'] ?? 0.0);
                    $marketValue = (float)($pos['estimated_value_total'] ?? ($qty * $avgEntry));

                    // Reverse resolve ISIN to Ticker if possible (fallback to ISIN)
                    $symbol = array_search($pos['isin'], $this->symbolToIsinMap) ?: $pos['isin'];

                    $positions[] = [
                        'symbol' => $symbol,
                        'isin' => $pos['isin'],
                        'name' => $pos['isin_title'] ?? $symbol,
                        'qty' => $qty,
                        'avg_entry_price' => $avgEntry,
                        'cost_basis' => $qty * $avgEntry,
                        'market_value' => $marketValue,
                        'current_price' => $qty > 0 ? ($marketValue / $qty) : $avgEntry,
                        'unrealized_pl' => $marketValue - ($qty * $avgEntry),
                    ];
                }
                return $positions;
            } else {
                Log::error("LemonMarkets Positions Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("LemonMarkets Positions Exception: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Fetch open position for a single asset.
     */
    public function getPosition(string $symbol): ?array
    {
        $positions = $this->getPositions();
        $symbol = strtoupper($symbol);
        $isin = $this->resolveSymbolToIsin($symbol);

        foreach ($positions as $pos) {
            if (strtoupper($pos['symbol']) === $symbol || ($isin && $pos['isin'] === $isin)) {
                return $pos;
            }
        }
        return null;
    }

    /**
     * Place an order (buy/sell).
     */
    public function placeOrder(string $symbol, float $qty, string $side, string $type = 'market', ?float $limitPrice = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Lemon.markets API not configured.'];
        }

        $isin = $this->resolveSymbolToIsin($symbol);
        if (!$isin) {
            return ['success' => false, 'message' => "No se pudo resolver el símbolo {$symbol} a un ISIN válido."];
        }

        $side = strtolower($side);
        // Lemon.markets expects integer quantities for some instruments, but we keep it float or cast to int if needed.
        // Usually, shares are whole quantities on European exchanges unless fractional shares are supported.
        $quantity = (int)round($qty);
        if ($quantity <= 0) {
            return ['success' => false, 'message' => "La cantidad aproximada debe ser al menos 1 acción completa para Lemon.markets."];
        }

        $body = [
            'isin' => $isin,
            'side' => $side,
            'quantity' => $quantity,
            'expires_at' => 'p7d', // Default order expiration of 7 days
        ];

        // Currently, Lemon.markets v1 uses POST /accounts/{account_id}/orders
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->post("{$this->baseUrl}/accounts/{$this->accountId}/orders", $body);

            if ($response->successful()) {
                $orderData = $response->json();
                $result = $orderData['results'] ?? $orderData;
                
                // Activating/Placing the order if it returns a created state that needs activation
                // Some versions of lemon.markets require a POST /orders/{order_id}/activate/
                // Let's check if the status is 'created' and if activation is needed.
                if (isset($result['id']) && isset($result['status']) && $result['status'] === 'created') {
                    $activateResponse = Http::withHeaders($this->getHeaders())
                        ->timeout(10)
                        ->post("{$this->baseUrl}/accounts/{$this->accountId}/orders/{$result['id']}/activate");
                    
                    if ($activateResponse->successful()) {
                        $activatedData = $activateResponse->json();
                        $result = $activatedData['results'] ?? $activatedData;
                    } else {
                        Log::warning("LemonMarkets Order Activation failed: " . $activateResponse->body());
                    }
                }

                return [
                    'success' => true,
                    'order' => [
                        'id' => $result['id'] ?? 'unknown',
                        'status' => $result['status'] ?? 'placed'
                    ]
                ];
            } else {
                $errorData = $response->json();
                $message = $errorData['error_message'] ?? $errorData['message'] ?? 'Error desconocido al colocar la orden en Lemon.markets.';
                Log::error("LemonMarkets Order Error: " . $response->body());
                return [
                    'success' => false,
                    'message' => $message
                ];
            }
        } catch (\Exception $e) {
            Log::error("LemonMarkets Order Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excepción de conexión con Lemon.markets: ' . $e->getMessage()
            ];
        }
    }
}
