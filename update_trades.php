<?php
require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t47 = \App\Models\Trade::find(47);
if ($t47) {
    $t47->broker_order_id = '676ca9a9-6050-4574-834d-4a205b5e5eec';
    $t47->status = 'accepted';
    $t47->save();
    echo "Trade 47 updated.\n";
}
$t48 = \App\Models\Trade::find(48);
if ($t48) {
    $t48->broker_order_id = '9c8b2fd5-8df7-4b5b-92af-4d9b26330f44';
    $t48->status = 'accepted';
    $t48->save();
    echo "Trade 48 updated.\n";
}
unlink(__FILE__);
