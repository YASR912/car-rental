
<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_rental');
define('DB_USER', 'root');
define('DB_PASS', '');
define('PROFILE_UPLOAD_PATH', 'uploads/profiles/');
define('CAR_UPLOAD_PATH', 'uploads/cars/');
if (!file_exists(PROFILE_UPLOAD_PATH)) {
    mkdir(PROFILE_UPLOAD_PATH, 0777, true);
}
if (!file_exists(CAR_UPLOAD_PATH)) {
    mkdir(CAR_UPLOAD_PATH, 0777, true);
}
// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get user data if logged in
function getUserData() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Function to handle file uploads
function handleFileUpload($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    $fileName = basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Generate unique filename to prevent overwriting
    $newFileName = uniqid() . '.' . $fileType;
    $targetFilePath = $targetDir . $newFileName;
    
    // Check if file type is allowed
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'error' => 'File size must be less than 5MB.'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return ['success' => true, 'file_name' => $newFileName];
    } else {
        return ['success' => false, 'error' => 'Sorry, there was an error uploading your file.'];
    }
}
?>