<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\TradingProviderInterface;
use App\Services\LemonMarketsService;

echo "=== Prueba de Integración con Lemon.markets ===\n";

$providerName = env('TRADING_PROVIDER', 'alpaca');
echo "Proveedor de Trading Activo en .env: {$providerName}\n";

$tradingService = app(TradingProviderInterface::class);

echo "Clase resuelta por la interfaz: " . get_class($tradingService) . "\n";

if ($tradingService instanceof LemonMarketsService) {
    echo "¡LemonMarketsService cargado correctamente!\n";
} else {
    echo "Proveedor activo es Alpaca. Creando instancia de LemonMarketsService manualmente para pruebas...\n";
    $tradingService = new LemonMarketsService();
}

echo "Configuración válida: " . ($tradingService->isConfigured() ? "SÍ" : "NO") . "\n";

if (!$tradingService->isConfigured()) {
    echo "Advertencia: Añade LEMON_API_KEY y LEMON_ACCOUNT_ID en el archivo .env para realizar pruebas de conexión.\n";
    exit(1);
}

echo "\n1. Consultando Información de Cuenta...\n";
$account = $tradingService->getAccountInfo();
if ($account) {
    echo "-> Conexión Exitosa.\n";
    echo "-> ID Cuenta: " . $account['account_number'] . "\n";
    echo "-> Efectivo (Cash): " . $account['cash'] . " " . $account['currency'] . "\n";
    echo "-> Valor Portafolio: " . $account['portfolio_value'] . " " . $account['currency'] . "\n";
} else {
    echo "-> [ERROR] No se pudo obtener la información de la cuenta.\n";
}

echo "\n2. Consultando Posiciones Abiertas...\n";
$positions = $tradingService->getPositions();
echo "-> Posiciones encontradas: " . count($positions) . "\n";
foreach ($positions as $pos) {
    echo "   - " . $pos['symbol'] . " (" . $pos['name'] . ") | Cantidad: " . $pos['qty'] . " | Precio Compra Avg: " . $pos['avg_entry_price'] . " | Valor Mercado: " . $pos['market_value'] . "\n";
}

echo "\n3. Resolviendo Símbolo AAPL a ISIN...\n";
$isin = $tradingService->resolveSymbolToIsin('AAPL');
echo "-> AAPL ISIN resuelto: " . ($isin ?? 'No resuelto') . "\n";

echo "\n4. Simulando una orden (ejecutar en dry-run si no queremos alterar saldo)...\n";
echo "-> Probando resolución y validación de orden para 1 acción de AAPL...\n";
// Simulación de compra
$res = $tradingService->placeOrder('AAPL', 1.0, 'buy');
echo "-> Resultado de la Orden: " . ($res['success'] ? 'Éxito' : 'Fallido') . "\n";
if (!$res['success']) {
    echo "   Detalle: " . ($res['message'] ?? 'Sin mensaje') . "\n";
} else {
    echo "   ID Orden: " . ($res['order']['id'] ?? 'Sin ID') . " | Estado: " . ($res['order']['status'] ?? 'Sin estado') . "\n";
}

echo "\n=== Fin del Test ===\n";
