<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AlpacaService;
use Illuminate\Support\Facades\Http;

$alpaca = new AlpacaService();
if (!$alpaca->isConfigured()) {
    echo "ERROR: Las credenciales de Alpaca no están configuradas.\n";
    exit(1);
}

$account = $alpaca->getAccountInfo();
$positions = $alpaca->getPositions();

// Fetch pending orders directly from Alpaca API for detailed reporting
$isPaper = config('services.alpaca.is_paper', true);
$baseUrl = $isPaper ? 'https://paper-api.alpaca.markets' : 'https://api.alpaca.markets';
$headers = [
    'APCA-API-KEY-ID' => config('services.alpaca.key_id'),
    'APCA-API-SECRET-KEY' => config('services.alpaca.secret_key'),
];

$ordersResponse = Http::withHeaders($headers)->get("{$baseUrl}/v2/orders", ['status' => 'open']);
$orders = $ordersResponse->successful() ? $ordersResponse->json() : [];

echo "=== ESTADO DE TU CARTERA DE SIMULACIÓN (ALPACA) ===\n";
echo "ID de Cuenta: " . ($account['account_number'] ?? 'N/A') . "\n";
echo "Efectivo Libre (Cash): $" . number_format((float)($account['cash'] ?? 0), 2) . "\n";
echo "Valor de la Cartera (Portfolio Value): $" . number_format((float)($account['portfolio_value'] ?? 0), 2) . "\n";
echo "Poder de Compra (Buying Power): $" . number_format((float)($account['buying_power'] ?? 0), 2) . "\n\n";

echo "--- ACCIONES EN POSICIÓN (COMPRADAS) ---\n";
if (empty($positions)) {
    echo "No tienes ninguna posición abierta actualmente (el mercado está cerrado y no se han ejecutado las compras todavía).\n";
} else {
    foreach ($positions as $pos) {
        echo "- " . $pos['symbol'] . ": " . $pos['qty'] . " acciones | Valor de mercado: $" . $pos['market_value'] . " | P&L: $" . $pos['unrealized_pl'] . "\n";
    }
}
echo "\n";

echo "--- ÓRDENES PENDIENTES (ESPERANDO APERTURA DEL MERCADO) ---\n";
if (empty($orders)) {
    echo "No hay órdenes pendientes.\n";
} else {
    foreach ($orders as $order) {
        echo "- Compra de " . $order['qty'] . " acciones de " . $order['symbol'] . " | Tipo: " . $order['type'] . " | Estado: " . $order['status'] . "\n";
    }
}
echo "==================================================\n";
