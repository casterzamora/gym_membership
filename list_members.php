<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$members = \App\Models\Member::all();
echo "Members found: " . count($members) . "\n";
foreach ($members as $m) {
    echo "ID: " . $m->id . ", Name: " . $m->first_name . " " . $m->last_name . ", Plan: " . $m->plan_id . "\n";
}
