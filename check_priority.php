<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('items');
if (in_array('priority', $columns)) {
    echo "Column 'priority' exists in 'items' table.\n";
} else {
    echo "Column 'priority' DOES NOT exist in 'items' table.\n";
}
