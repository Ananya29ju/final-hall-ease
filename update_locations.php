<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Hall;

$halls = Hall::where('location', 'LIKE', '% Block')->get();
echo "Found " . $halls->count() . " halls to update.\n";

foreach ($halls as $hall) {
    $old = $hall->location;
    $hall->location = str_replace(' Block', '', $hall->location);
    $hall->save();
    echo "Updated '{$old}' to '{$hall->location}'\n";
}

echo "Done.\n";
