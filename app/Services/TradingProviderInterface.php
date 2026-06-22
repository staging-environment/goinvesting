<?php

namespace App\Services;

interface TradingProviderInterface
{
    /**
     * Check if the API credentials are configured.
     */
    public function isConfigured(): bool;

    /**
     * Fetch account details.
     * Must return an array with 'cash' and 'portfolio_value' keys.
     */
    public function getAccountInfo(): ?array;

    /**
     * Fetch all open positions.
     * Must return an array of positions, where each position has:
     * - 'symbol' (string)
     * - 'qty' (float)
     * - 'avg_entry_price' (float)
     * - 'cost_basis' (float)
     * - 'market_value' (float)
     */
    public function getPositions(): array;

    /**
     * Fetch open position for a single asset.
     */
    public function getPosition(string $symbol): ?array;

    /**
     * Place an order (buy/sell).
     * Must return an array:
     * - 'success' (bool)
     * - 'order' (optional array with 'id')
     * - 'message' (optional string in case of failure)
     */
    public function placeOrder(string $symbol, float $qty, string $side, string $type = 'market', ?float $limitPrice = null): array;
    
    /**
     * Cancel a specific order by its broker ID.
     */
    public function cancelOrder(string $orderId): array;

    /**
     * Check if the market is currently open.
     */
    public function isMarketOpen(): bool;
}
