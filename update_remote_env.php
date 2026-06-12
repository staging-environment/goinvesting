<?php

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $content = file_get_contents($envFile);
    
    // Replace APP_URL
    $content = preg_replace('/APP_URL=.*/', 'APP_URL="https://goinvesting.es"', $content);
    
    file_put_contents($envFile, $content);
    echo "SUCCESS: APP_URL updated to https://goinvesting.es in .env\n";
} else {
    echo "ERROR: .env file not found\n";
}
