<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AlpacaService;

$alpaca = new AlpacaService();
if (!$alpaca->isConfigured()) {
    echo "ERROR: Las credenciales de Alpaca no están configuradas.\n";
    exit(1);
}

$symbol = 'BTC/USD';
$qty = 0.01;
$side = 'buy';

echo "Enviando orden de COMPRA de {$qty} de {$symbol}...\n";
$result = $alpaca->placeOrder($symbol, $qty, $side);

if ($result['success']) {
    echo "¡ORDEN REALIZADA CON ÉXITO!\n";
    print_r($result['order']);
} else {
    echo "ERROR al colocar la orden: " . $result['message'] . "\n";
}
