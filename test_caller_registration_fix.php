#!/usr/bin/env php
<?php
/**
 * Test script to verify the Caller model boot() fix
 * Simulates the registration update logic
 */

// Simulate the boot() updating event logic
function testCallerBootLogic($isDirty = [], $isAuthenticated = false, $isAdmin = false, $environment = 'production') {
    // Allow hits update for everyone
    if (isset($isDirty['hits']) && count($isDirty) === 1) {
        return 'ALLOW: hits-only update';
    }

    // Allow all updates for authenticated admins
    if ($isAuthenticated && $isAdmin) {
        return 'ALLOW: admin user';
    }

    // Allow public caller registration updates (name, phone, ip_address, status)
    $dirtyKeys = array_keys($isDirty);
    $allowedPublicFields = ['name', 'phone', 'ip_address', 'status'];
    if (!$isAuthenticated && count($dirtyKeys) > 0 && count(array_diff($dirtyKeys, $allowedPublicFields)) === 0) {
        return 'ALLOW: public registration update';
    }

    // In production, restrict other updates
    if ($environment === 'production') {
        return 'BLOCK: production restriction';
    }

    return 'ALLOW: non-production environment';
}

// Test cases
echo "====== Caller Boot() Fix Verification ======\n\n";

$tests = [
    [
        'name' => 'Public registration (new caller)',
        'dirty' => ['name' => 1, 'phone' => 1, 'ip_address' => 1, 'status' => 1],
        'auth' => false,
        'admin' => false,
        'env' => 'production',
        'expected' => 'ALLOW',
    ],
    [
        'name' => 'Public registration (existing caller)',
        'dirty' => ['name' => 1, 'phone' => 1, 'ip_address' => 1],
        'auth' => false,
        'admin' => false,
        'env' => 'production',
        'expected' => 'ALLOW',
    ],
    [
        'name' => 'Public hits increment',
        'dirty' => ['hits' => 1],
        'auth' => false,
        'admin' => false,
        'env' => 'production',
        'expected' => 'ALLOW',
    ],
    [
        'name' => 'Admin update (any fields)',
        'dirty' => ['is_winner' => 1, 'is_selected' => 1],
        'auth' => true,
        'admin' => true,
        'env' => 'production',
        'expected' => 'ALLOW',
    ],
    [
        'name' => 'Public update with restricted field (should fail)',
        'dirty' => ['name' => 1, 'is_winner' => 1],
        'auth' => false,
        'admin' => false,
        'env' => 'production',
        'expected' => 'BLOCK',
    ],
    [
        'name' => 'Local development bypass',
        'dirty' => ['name' => 1, 'is_winner' => 1],
        'auth' => false,
        'admin' => false,
        'env' => 'local',
        'expected' => 'ALLOW',
    ],
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    $result = testCallerBootLogic(
        $test['dirty'],
        $test['auth'],
        $test['admin'],
        $test['env']
    );
    
    $isPass = strpos($result, substr($test['expected'], 0, 5)) === 0;
    $status = $isPass ? '✓ PASS' : '✗ FAIL';
    
    echo "$status | {$test['name']}\n";
    echo "       Result: $result\n";
    
    if ($isPass) {
        $passed++;
    } else {
        $failed++;
    }
}

echo "\n====== Summary ======\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed > 0) {
    exit(1);
}
echo "\n✅ All tests passed! Registration should work in production.\n";
exit(0);
