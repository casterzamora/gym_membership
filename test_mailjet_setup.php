<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

echo "=== Testing Mailjet Email Setup ===\n\n";

echo "Current Mail Config:\n";
echo "  Mailer: " . config('mail.default') . "\n";
echo "  From Address: " . config('mail.from.address') . "\n";
echo "  From Name: " . config('mail.from.name') . "\n";
echo "  Mailjet API Key: " . (env('MAILJET_APIKEY') ? '✓ Set' : '✗ Missing') . "\n";
echo "  Mailjet Secret: " . (env('MAILJET_APISECRET') ? '✓ Set' : '✗ Missing') . "\n\n";

try {
    echo "Sending test email via Mailjet...\n";
    
    Mail::raw('GymFlow Mailjet Test: If you receive this, real email sending is working!', function (Message $m) {
        $m->to('casterzamora1@gmail.com')
          ->subject('✓ GymFlow Mailjet Test - Email Setup Verified');
    });
    
    echo "✅ Test email sent successfully via Mailjet!\n";
    echo "\nCheck your email at casterzamora1@gmail.com for confirmation.\n";
    echo "If received, your Mailjet setup is complete and ready for production.\n";
    
} catch (\Exception $e) {
    echo "❌ Error sending email:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "  Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}
