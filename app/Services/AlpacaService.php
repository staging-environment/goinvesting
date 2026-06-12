<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlpacaService
{
    protected string $keyId;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.alpaca.key_id') ?? '';
        $this->secretKey = config('services.alpaca.secret_key') ?? '';
        
        $isPaper = config('services.alpaca.is_paper', true);
        $this->baseUrl = $isPaper 
            ? 'https://paper-api.alpaca.markets' 
            : 'https://api.alpaca.markets';
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
    public function getAccountInfo()
    {
        if (!$this->isConfigured()) return null;

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/v2/account");

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
    public function getPositions()
    {
        if (!$this->isConfigured()) return [];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/v2/positions");

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
    public function getPosition(string $symbol)
    {
        if (!$this->isConfigured()) return null;

        $symbol = strtoupper($symbol);
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/v2/positions/{$symbol}");

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
    public function placeOrder(string $symbol, float $qty, string $side, string $type = 'market', float $limitPrice = null)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Alpaca API not configured.'];
        }

        $symbol = strtoupper($symbol);
        $side = strtolower($side);
        $type = strtolower($type);

        $body = [
            'symbol' => $symbol,
            'qty' => (string)$qty,
            'side' => $side,
            'type' => $type,
            'time_in_force' => 'day'
        ];

        if ($type === 'limit' && $limitPrice) {
            $body['limit_price'] = (string)$limitPrice;
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->post("{$this->baseUrl}/v2/orders", $body);

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
