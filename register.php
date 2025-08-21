
<?php
require_once 'includes/config.php';

$name = $email = $qualification = $address = $phone = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $qualification = sanitizeInput($_POST['qualification']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
     $profileImage = 'default_profile.jpg';
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $uploadResult = handleFileUpload($_FILES['profile_image'], PROFILE_UPLOAD_PATH);
    if ($uploadResult['success']) {
        $profileImage = $uploadResult['file_name'];
    } else {
        $errors[] = $uploadResult['error'];
    }
}
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already registered";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, qualification, address, phone, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$name, $email, $hashed_password, $qualification, $address, $phone, $profileImage])) {
    $_SESSION['success'] = "Registration successful. Please login.";
    header("Location: login.php");
    exit();
} else {
    $errors[] = "Something went wrong. Please try again.";
}
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Register</h2>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="mb-3">
                <label for="qualification" class="form-label">Qualification</label>
                <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo $qualification; ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address"><?php echo $address; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
         <div class="col-md-4">
          <div class="mb-3 text-center">
            <label for="profile_image" class="form-label">Profile Image</label>
            <div class="image-upload-container">
                <img id="profilePreview" src="<?php echo PROFILE_UPLOAD_PATH; ?>default_profile.jpg" class="img-thumbnail mb-2" alt="Profile Preview" style="width: 150px; height: 150px; object-fit: cover;">
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
            </div>
           </div>
          </div>
       </form>
    </div>
</div>
<script>
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
<?php
require_once 'includes/footer.php';
?>