<?php
/**
 * Test Script: Send Winner Announcement Email
 * 
 * This script:
 * 1. Loads Laravel application
 * 2. Retrieves all current winners from database
 * 3. Sends email to admin addresses for testing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Caller;
use App\Mail\WinnerAnnouncement;
use Illuminate\Support\Facades\Mail;

echo "=== Winner Announcement Email Test ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Get all winners
$winners = Caller::where('is_winner', true)->orderBy('created_at', 'desc')->get();

echo "Total Winners in Database: " . $winners->count() . "\n";
echo str_repeat("-", 60) . "\n";

if ($winners->count() === 0) {
    echo "❌ No winners found in database!\n";
    echo "Please ensure winners are marked with is_winner = 1\n";
    exit(1);
}

// Display winners
$winners->each(function($w, $i) {
    echo ($i+1) . ". " . $w->name . "\n";
    echo "   CPR: " . substr($w->cpr, 0, 3) . "****" . substr($w->cpr, -2) . "\n";
    echo "   Phone: " . $w->phone . "\n";
    echo "   Hits: " . $w->hits . "\n";
    echo "   Date: " . $w->created_at->format('Y-m-d H:i:s') . "\n\n";
});

echo str_repeat("=", 60) . "\n";
echo "Sending email to admin addresses...\n";
echo str_repeat("=", 60) . "\n";

// Send email
try {
    Mail::send(new WinnerAnnouncement($winners));
    echo "✅ Email sent successfully!\n";
    echo "Recipients: aldoyh@gmail.com, alsaryatv@gmail.com\n";
    echo "From: " . config('mail.from.address') . "\n";
    echo "Subject: تقرير الفائزين - برنامج السارية\n";
    echo "\nEmail contains:\n";
    echo "- Total Winners: " . $winners->count() . "\n";
    echo "- Total Hits: " . $winners->sum('hits') . "\n";
    echo "- Responsive design (desktop table + mobile cards)\n";
    echo "\n✅ Test complete!\n";
} catch (\Exception $e) {
    echo "❌ Error sending email:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
