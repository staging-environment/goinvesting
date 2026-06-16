<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AlpacaService;

$alpaca = new AlpacaService();
if (!$alpaca->isConfigured()) {
    echo "ERROR: Las credenciales de Alpaca no están configuradas en el archivo .env\n";
    exit(1);
}

echo "Conectando con Alpaca...\n";
$info = $alpaca->getAccountInfo();

if ($info) {
    echo "¡CONEXIÓN EXITOSA!\n";
    echo "ID de Cuenta: " . ($info['id'] ?? 'N/A') . "\n";
    echo "Estado: " . ($info['status'] ?? 'N/A') . "\n";
    echo "Moneda: " . ($info['currency'] ?? 'N/A') . "\n";
    echo "Capital Total (Portfolio Value): $" . ($info['portfolio_value'] ?? '0.00') . "\n";
    echo "Efectivo Disponible (Buying Power): $" . ($info['buying_power'] ?? '0.00') . "\n";
} else {
    echo "ERROR: No se pudo conectar con Alpaca. Revisa tus credenciales (ALPACA_KEY_ID y ALPACA_SECRET_KEY) y si ALPACA_IS_PAPER está configurado correctamente.\n";
}
