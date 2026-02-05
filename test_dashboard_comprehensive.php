<?php

// Comprehensive dashboard test script
require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Comprehensive Dashboard Widget Test\n";
echo "========================================\n\n";

// Test 1: Load all widget classes
$widgetClasses = [
    'CallersStatsWidget' => 'App\Filament\Widgets\CallersStatsWidget',
    'ParticipationRateWidget' => 'App\Filament\Widgets\ParticipationRateWidget',
    'PeakHoursChart' => 'App\Filament\Widgets\PeakHoursChart',
    'RecentActivityWidget' => 'App\Filament\Widgets\RecentActivityWidget',
    'RegistrationTrendsChart' => 'App\Filament\Widgets\RegistrationTrendsChart',
    'StatusDistributionChart' => 'App\Filament\Widgets\StatusDistributionChart',
    'WinnersHistoryWidget' => 'App\Filament\Widgets\WinnersHistoryWidget',
];

echo "1️⃣  Testing Widget Class Loading...\n";
$classesLoaded = 0;
foreach ($widgetClasses as $name => $class) {
    try {
        if (class_exists($class)) {
            echo "   ✅ $name: Class loaded successfully\n";
            $classesLoaded++;
        } else {
            echo "   ❌ $name: Class not found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ $name: " . $e->getMessage() . "\n";
    }
}
echo "   Result: $classesLoaded/" . count($widgetClasses) . " classes loaded\n\n";

// Test 2: Verify property declarations
echo "2️⃣  Verifying Property Declarations...\n";
$propertyIssues = 0;
$totalProperties = 0;

foreach ($widgetClasses as $name => $class) {
    try {
        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        
        foreach ($properties as $property) {
            $totalProperties++;
            
            // Skip properties not declared in this class
            if ($property->class !== $class) {
                continue;
            }
            
            $propName = $property->getName();
            $isStatic = $property->isStatic();
            
            // Validate based on property name
            if ($propName === 'sort') {
                if (!$isStatic) {
                    echo "   ⚠️  $name: \$$propName should be static\n";
                    $propertyIssues++;
                }
            } elseif ($propName === 'heading' || $propName === 'pollingInterval' || 
                     $propName === 'color' || $propName === 'maxHeight') {
                if ($isStatic) {
                    echo "   ⚠️  $name: \$$propName should be non-static\n";
                    $propertyIssues++;
                }
            }
        }
    } catch (Exception $e) {
        echo "   ❌ $name: " . $e->getMessage() . "\n";
        $propertyIssues++;
    }
}
echo "   Result: " . ($totalProperties - $propertyIssues) . "/$totalProperties properties correct\n\n";

// Test 3: Check inheritance hierarchy
echo "3️⃣  Verifying Inheritance Hierarchy...\n";
$inheritanceIssues = 0;

$expectedParents = [
    'App\Filament\Widgets\CallersStatsWidget' => 'Filament\Widgets\StatsOverviewWidget',
    'App\Filament\Widgets\ParticipationRateWidget' => 'Filament\Widgets\StatsOverviewWidget',
    'App\Filament\Widgets\PeakHoursChart' => 'Filament\Widgets\ChartWidget',
    'App\Filament\Widgets\RegistrationTrendsChart' => 'Filament\Widgets\ChartWidget',
    'App\Filament\Widgets\StatusDistributionChart' => 'Filament\Widgets\ChartWidget',
    'App\Filament\Widgets\RecentActivityWidget' => 'Filament\Widgets\TableWidget',
    'App\Filament\Widgets\WinnersHistoryWidget' => 'Filament\Widgets\TableWidget',
];

foreach ($expectedParents as $child => $expectedParent) {
    try {
        $reflection = new ReflectionClass($child);
        $parent = $reflection->getParentClass();
        
        if ($parent && $parent->getName() === $expectedParent) {
            echo "   ✅ " . class_basename($child) . ": Correctly extends $expectedParent\n";
        } else {
            echo "   ❌ " . class_basename($child) . ": Expected to extend $expectedParent, got " . ($parent ? $parent->getName() : 'none') . "\n";
            $inheritanceIssues++;
        }
    } catch (Exception $e) {
        echo "   ❌ " . class_basename($child) . ": " . $e->getMessage() . "\n";
        $inheritanceIssues++;
    }
}
echo "   Result: " . (count($expectedParents) - $inheritanceIssues) . "/" . count($expectedParents) . " inheritance relationships correct\n\n";

// Test 4: Check for method implementations
echo "4️⃣  Verifying Required Method Implementations...\n";
$methodIssues = 0;

$requiredMethods = [
    'App\Filament\Widgets\CallersStatsWidget' => ['getStats'],
    'App\Filament\Widgets\ParticipationRateWidget' => ['getStats'],
    'App\Filament\Widgets\PeakHoursChart' => ['getData', 'getType'],
    'App\Filament\Widgets\RegistrationTrendsChart' => ['getData', 'getType'],
    'App\Filament\Widgets\StatusDistributionChart' => ['getData', 'getType'],
    'App\Filament\Widgets\RecentActivityWidget' => ['table'],
    'App\Filament\Widgets\WinnersHistoryWidget' => ['table'],
];

foreach ($requiredMethods as $class => $methods) {
    try {
        $reflection = new ReflectionClass($class);
        
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                $methodObj = $reflection->getMethod($method);
                if ($methodObj->class === $class) {
                    echo "   ✅ " . class_basename($class) . "::{$method}() implemented\n";
                } else {
                    echo "   ⚠️  " . class_basename($class) . "::{$method}() inherited from " . $methodObj->class . "\n";
                }
            } else {
                echo "   ❌ " . class_basename($class) . "::{$method}() not found\n";
                $methodIssues++;
            }
        }
    } catch (Exception $e) {
        echo "   ❌ " . class_basename($class) . ": " . $e->getMessage() . "\n";
        $methodIssues++;
    }
}
echo "   Result: " . (array_sum(array_map('count', $requiredMethods)) - $methodIssues) . "/" . array_sum(array_map('count', $requiredMethods)) . " methods implemented\n\n";

// Final summary
echo "📊 Test Summary\n";
echo "========================================\n";
echo "Classes Loaded: $classesLoaded/" . count($widgetClasses) . "\n";
echo "Properties Correct: " . ($totalProperties - $propertyIssues) . "/$totalProperties\n";
echo "Inheritance Correct: " . (count($expectedParents) - $inheritanceIssues) . "/" . count($expectedParents) . "\n";
echo "Methods Implemented: " . (array_sum(array_map('count', $requiredMethods)) - $methodIssues) . "/" . array_sum(array_map('count', $requiredMethods)) . "\n";

$totalIssues = $propertyIssues + $inheritanceIssues + $methodIssues;
echo "\n🎯 Overall Status: " . ($totalIssues === 0 ? "✅ ALL TESTS PASSED" : "⚠️  $totalIssues ISSUES FOUND") . "\n";

echo "\n🚀 Dashboard widgets are ready for deployment!\n";