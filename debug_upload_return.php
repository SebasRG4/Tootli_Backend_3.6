<?php

use App\CentralLogics\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Create a fake image
$image = UploadedFile::fake()->image('test_review.png');

// Call upload with trailing slash (simulating the state when ID 6 was created)
$resultWithSlash = Helpers::upload('review/', 'png', $image);
echo "Result with 'review/': " . $resultWithSlash . "\n";

// Call upload without trailing slash (my fix)
$resultWithoutSlash = Helpers::upload('review', 'png', $image);
echo "Result with 'review': " . $resultWithoutSlash . "\n";

// Check what actually happens on disk logic-wise (mocking or inferring from code is better, but this runs actual code)
