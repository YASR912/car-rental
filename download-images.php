<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set longer execution time for downloading files
set_time_limit(300); // 5 minutes

// Function to download file with error handling
function downloadFile($url, $path) {
    // Create directory if it doesn't exist
    $dir = dirname($path);
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            return ['success' => false, 'error' => "Failed to create directory: $dir"];
        }
    }
    
    // Check if file already exists
    if (file_exists($path)) {
        return ['success' => true, 'message' => "File already exists: $path", 'skipped' => true];
    }
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    // Execute request
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check for errors
    if ($data === false) {
        return ['success' => false, 'error' => "cURL error: $error"];
    }
    
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP error: $httpCode"];
    }
    
    // Save file
    if (file_put_contents($path, $data) === false) {
        return ['success' => false, 'error' => "Failed to write file: $path"];
    }
    
    return ['success' => true, 'message' => "Downloaded: $path"];
}

// List of images to download with direct URLs
$images = [
    // Car images
    'uploads/cars/toyota_camry.jpg' => 'https://images.unsplash.com/photo-1549399542-7e82138bd0ca',
    'uploads/cars/honda_crv.jpg' => 'https://images.unsplash.com/photo-1549399815872-7e0d5a3ba042',
    'uploads/cars/ford_mustang.jpg' => 'https://images.unsplash.com/photo-1542362567-b07e54358753',
    'uploads/cars/tesla_model3.jpg' => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89',
    'uploads/cars/jeep_wrangler.jpg' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4',
    'uploads/cars/bmw_x5.jpg' => 'https://images.unsplash.com/photo-1544636330-7fef5d899cc2',
    'uploads/cars/audi_a4.jpg' => 'https://images.unsplash.com/photo-1606152536277-5aa1fd33e150',
    'uploads/cars/mercedes_cclass.jpg' => 'https://images.unsplash.com/photo-1622933017702-31b6e9129582',
    
    // Profile image
    'uploads/profiles/default_profile.jpg' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde',
    
    // Hero images
    'images/hero-bg.jpg' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70',
    'images/hero-car.png' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000'
];

// Process download if requested
if (isset($_POST['download'])) {
    $results = [];
    $total = count($images);
    $processed = 0;
    
    header('Content-Type: application/json');
    
    foreach ($images as $path => $url) {
        $processed++;
        $result = downloadFile($url, $path);
        
        if ($result['success']) {
            if (isset($result['skipped'])) {
                $results[] = ['type' => 'warning', 'text' => $result['message']];
            } else {
                $results[] = ['type' => 'success', 'text' => $result['message']];
            }
        } else {
            $results[] = ['type' => 'error', 'text' => $result['error'] . " (File: $path)"];
        }
        
        // Flush output for progress tracking
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
    
    // Add summary message
    $successCount = count(array_filter($results, function($r) { 
        return $r['type'] === 'success' || $r['type'] === 'warning'; 
    }));
    $errorCount = count(array_filter($results, function($r) { 
        return $r['type'] === 'error'; 
    }));
    
    $results[] = [
        'type' => 'info', 
        'text' => "Download complete. Success: $successCount, Errors: $errorCount"
    ];
    
    echo json_encode(['messages' => $results]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Image Download</title>
</head>
<body>
    <h1>Image Download Script</h1>
    <p>This PHP script is designed to be called via AJAX from the HTML interface.</p>
</body>
</html>