<?php

// Quick database check
$db = new PDO('sqlite:database/alsaryatv.sqlite');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== Database State ===\n";

$tables = ['users', 'callers'];
foreach ($tables as $table) {
    try {
        $result = $db->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch();
        echo "$table: ".($row['count'] ?? 'N/A')." rows\n";
    } catch (Exception $e) {
        echo "$table: Error - ".$e->getMessage()."\n";
    }
}

// Check users
echo "\n=== Users Sample ===\n";
$result = $db->query('SELECT name, email, role FROM users LIMIT 3');
foreach ($result->fetchAll() as $row) {
    echo "- {$row['name']} ({$row['email']}) [{$row['role']}]\n";
}

// Check callers
echo "\n=== Callers Sample ===\n";
$result = $db->query('SELECT name, phone, hits FROM callers LIMIT 3');
foreach ($result->fetchAll() as $row) {
    echo "- {$row['name']} ({$row['phone']}) hits: {$row['hits']}\n";
}

echo "\nDone.\n";
