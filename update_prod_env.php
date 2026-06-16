<?php
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    $content = preg_replace('/APP_URL=.*/', 'APP_URL="https://goinvesting.es"', $content);
    file_put_contents($envPath, $content);
    echo "Successfully updated APP_URL in .env\n";
} else {
    echo ".env file not found\n";
}
unlink(__FILE__);
