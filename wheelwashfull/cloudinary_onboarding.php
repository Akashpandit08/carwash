<?php
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;

// Configure Cloudinary
// <- replace this
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dpurvsfnv',
        'api_key'  => '581881422472284',
        'api_secret' => 'MC-yu9yGkahJ5-5s1x1xUIQuHrE'
    ],
    'url' => [
        'secure' => true
    ]
]);

echo "Uploading sample image...\n";
// Upload an image
$uploadResult = $cloudinary->uploadApi()->upload('https://res.cloudinary.com/demo/image/upload/sample.jpg', [
    'public_id' => 'onboarding_sample'
]);

echo "Secure URL: " . $uploadResult['secure_url'] . "\n";
echo "Public ID: " . $uploadResult['public_id'] . "\n\n";

// Get image details
echo "--- Image Details ---\n";
echo "Width: " . $uploadResult['width'] . "px\n";
echo "Height: " . $uploadResult['height'] . "px\n";
echo "Format: " . $uploadResult['format'] . "\n";
echo "File size: " . $uploadResult['bytes'] . " bytes\n\n";

// Transform the image
// f_auto: Automatically chooses the best format for the browser
// q_auto: Automatically adjusts quality to balance visual fidelity and file size
$transformedUrl = $cloudinary->image('onboarding_sample')
    ->format('auto')
    ->quality('auto')
    ->toUrl();

echo "Done! Click link below to see optimized version of the image. Check the size and the format.\n";
echo $transformedUrl . "\n";
