<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Donation;
use App\Models\Project;

$totalAmil = Donation::sum('amil_amount');
echo "Total Amil Global: " . $totalAmil . "\n";

$projects = Project::all();
foreach($projects as $p) {
    echo "Project: [" . $p->name . "] (ID: " . $p->id . ")\n";
}
