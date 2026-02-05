<?php

use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;

// The file we found on disk
$filename = 'InBoSh6R2rSxKmTvgDjY0iIO8W5hJYXgHOacVhCr.jpg';
$path = 'review/' . $filename;

echo "Checking file: $path\n";

// Check visibility on Public disk
$exists = Storage::disk('public')->exists($path);
echo "Storage::disk('public')->exists('$path'): " . ($exists ? 'YES' : 'NO') . "\n";

// Check Helper
$url = Helpers::get_full_url('review', $filename, 'public');
echo "Helpers::get_full_url result: " . json_encode($url) . "\n";

// Check directory listing via Storage
$files = Storage::disk('public')->files('review');
echo "Files in review directory via Storage: \n";
print_r($files);

// Check if case sensitivity is an issue
if (!$exists) {
    echo "Files found on disk:\n";
    foreach ($files as $f) {
        if (strtolower($f) == strtolower($path)) {
            echo "Match found with different casing: $f\n";
        }
    }
}
