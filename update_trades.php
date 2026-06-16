<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\Trade::whereNull('bot_execution_id')->update(['is_dry_run' => true]);
echo "Updated manual trades successfully\n";
