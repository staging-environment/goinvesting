<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlpacaService implements TradingProviderInterface
{
    protected ?string $accountId;
    protected bool $isBroker = false;

    public function __construct(
        ?string $keyId = null,
        ?string $secretKey = null,
        ?string $accountId = null,
        ?bool $isPaper = null
    ) {
        $this->keyId = $keyId ?? config('services.alpaca.key_id') ?? '';
        $this->secretKey = $secretKey ?? config('services.alpaca.secret_key') ?? '';
        $this->accountId = $accountId ?? config('services.alpaca.account_id');
        
        $isPaper = $isPaper ?? config('services.alpaca.is_paper', true);
        
        if ($this->accountId) {
            $this->isBroker = true;
            $this->baseUrl = $isPaper 
                ? "https://broker-api.sandbox.alpaca.markets/v1/trading/accounts/{$this->accountId}" 
                : "https://broker-api.alpaca.markets/v1/trading/accounts/{$this->accountId}";
        } else {
            $this->baseUrl = $isPaper 
                ? 'https://paper-api.alpaca.markets' 
                : 'https://api.alpaca.markets';
        }
    }

    /**
     * Helper to get request headers.
     */
    protected function getHeaders(): array
    {
        return [
            'APCA-API-KEY-ID' => $this->keyId,
            'APCA-API-SECRET-KEY' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Check if API credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->secretKey);
    }

    /**
     * Fetch account details.
     */
    public function getAccountInfo(): ?array
    {
        if (!$this->isConfigured()) return null;

        $endpoint = $this->isBroker ? "/account" : "/v2/account";
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Alpaca Account Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Alpaca API Exception: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Fetch all open positions.
     */
    public function getPositions(): array
    {
        if (!$this->isConfigured()) return [];

        $endpoint = $this->isBroker ? "/positions" : "/v2/positions";
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Alpaca Positions Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Alpaca API Exception: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Fetch open position for a single asset.
     */
    public function getPosition(string $symbol): ?array
    {
        if (!$this->isConfigured()) return null;

        $symbol = strtoupper($symbol);
        $endpoint = $this->isBroker ? "/positions/{$symbol}" : "/v2/positions/{$symbol}";
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Will return 404 if position is not open, which is normal.
        }
        return null;
    }

    /**
     * Place an order (buy/sell).
     */
    public function placeOrder(string $symbol, float $qty, string $side, string $type = 'market', ?float $limitPrice = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Alpaca API not configured.'];
        }

        $symbol = strtoupper($symbol);
        $side = strtolower($side);
        $type = strtolower($type);

        $isCrypto = str_contains($symbol, 'BTC') || str_contains($symbol, 'ETH') || str_contains($symbol, '/');

        $body = [
            'symbol' => $symbol,
            'qty' => (string)$qty,
            'side' => $side,
            'type' => $type,
            'time_in_force' => $isCrypto ? 'gtc' : 'day'
        ];

        if ($type === 'limit' && $limitPrice) {
            $body['limit_price'] = (string)$limitPrice;
        }

        $endpoint = $this->isBroker ? "/orders" : "/v2/orders";
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->post("{$this->baseUrl}{$endpoint}", $body);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'order' => $response->json()
                ];
            } else {
                $errorData = $response->json();
                return [
                    'success' => false,
                    'message' => $errorData['message'] ?? 'Error desconocido al colocar la orden.'
                ];
            }
        } catch (\Exception $e) {
            Log::error("Alpaca Order Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excepción de conexión con Alpaca: ' . $e->getMessage()
            ];
        }
    }
}
