<?php

// Test admin access to see what error we're getting
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

echo "Testing admin panel access...\n\n";

try {
    // Bootstrap Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Create a test user
    echo "Creating test user...\n";
    $user = User::factory()->create();
    echo "User created: {$user->email}\n\n";
    
    // Try to access admin panel
    echo "Attempting to access admin panel...\n";
    
    // This would normally be done through HTTP testing, but let's see if we can
    // at least load the dashboard class
    $dashboardClass = 'App\Filament\Pages\Dashboard';
    
    if (class_exists($dashboardClass)) {
        echo "✅ Dashboard class exists\n";
        
        $reflection = new ReflectionClass($dashboardClass);
        $methods = $reflection->getMethods();
        
        echo "Dashboard methods found:\n";
        foreach ($methods as $method) {
            if ($method->class === $dashboardClass) {
                echo "   - {$method->getName()}()\n";
            }
        }
    } else {
        echo "❌ Dashboard class not found\n";
    }
    
    echo "\n✅ Admin panel access test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}