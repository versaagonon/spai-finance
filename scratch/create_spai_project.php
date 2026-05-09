<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Project;

Project::firstOrCreate(
    ['name' => 'SPAI'],
    [
        'pilar' => 'Operasional',
        'target_amount' => 0,
        'description' => 'Proyek Internal untuk pengelolaan Hak Amil dan Operasional SPAI'
    ]
);
echo "Project SPAI created or already exists.\n";
