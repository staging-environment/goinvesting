<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::first();
echo "Today: " . \Carbon\Carbon::today()->toDateString() . "\n";
echo "SQL Query example:\n";
$query = $user->trades()
    ->whereDate('created_at', \Carbon\Carbon::today())
    ->where('is_dry_run', false)
    ->where('side', 'buy');
echo $query->toSql() . "\n";
echo "Bindings: " . implode(', ', $query->getBindings()) . "\n";

$trades = $query->get();
echo "Trades found: " . $trades->count() . "\n";
foreach ($trades as $t) {
    echo "ID: {$t->id}, Symbol: {$t->symbol}, Created At: {$t->created_at}, Cost: " . ($t->qty * $t->price) . "\n";
}
