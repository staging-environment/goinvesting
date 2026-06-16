<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'developer@goinvesting.es')->first() ?? App\Models\User::first();
if (!$user) {
    echo "No users found\n";
    exit;
}

echo "Logged in as User: " . $user->name . " (" . $user->email . ")\n";
auth()->login($user);

$tradingService = app(App\Services\TradingProviderInterface::class);
if (!$tradingService->isConfigured()) {
    echo "Trading service not configured\n";
    exit;
}

echo "Base URL: " . $tradingService->baseUrl . "\n";
echo "Account Info:\n";
print_r($tradingService->getAccountInfo());

echo "\nPositions:\n";
print_r($tradingService->getPositions());

// Fetch all open orders
$headers = [
    'APCA-API-KEY-ID' => $user->alpaca_key_id,
    'APCA-API-SECRET-KEY' => $user->alpaca_secret_key,
    'Content-Type' => 'application/json',
];
if ($user->alpaca_account_id) {
    $baseUrl = "https://broker-api.sandbox.alpaca.markets/v1/trading/accounts/{$user->alpaca_account_id}";
    $endpoint = "{$baseUrl}/orders";
} else {
    $baseUrl = "https://paper-api.alpaca.markets";
    $endpoint = "{$baseUrl}/v2/orders";
}

echo "\nFetching open orders from: $endpoint\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($endpoint);
if ($response->successful()) {
    print_r($response->json());
} else {
    echo "Error fetching orders: " . $response->body() . "\n";
}
