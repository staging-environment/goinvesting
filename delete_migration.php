<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\Illuminate\Support\Facades\DB::table('migrations')->where('migration', '2026_07_13_161733_add_trailing_and_dca_fields_to_trades')->delete();
echo "Migration entry deleted successfully!\n";
