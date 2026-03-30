<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Hall;

$halls = Hall::select('campus_name', 'location')->distinct()->get();
echo "Total distinct Campus|Block pairs: " . $halls->count() . "\n";

foreach ($halls as $hall) {
    echo "- {$hall->campus_name} | {$hall->location}\n";
}
