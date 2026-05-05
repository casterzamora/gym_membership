<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw(
        'GymFlow SMTP test: if you receive this, real email sending is working.',
        function ($message) {
            $message->to('casterzamora1@gmail.com')
                ->subject('GymFlow SMTP Test');
        }
    );

    echo "MAIL_SENT\n";
} catch (\Throwable $e) {
    echo "MAIL_ERROR: " . $e->getMessage() . "\n";
}
