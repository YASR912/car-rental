

<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = getUserData();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $qualification = sanitizeInput($_POST['qualification']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validation

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $uploadResult = handleFileUpload($_FILES['profile_image'], PROFILE_UPLOAD_PATH);
    if ($uploadResult['success']) {
        
        if ($user['profile_image'] != 'default_profile.jpg') {
            @unlink(PROFILE_UPLOAD_PATH . $user['profile_image']);
        }
        $profileImage = $uploadResult['file_name'];
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$profileImage, $_SESSION['user_id']]);
        $success = "Profile image updated successfully!";
    } else {
        $errors[] = $uploadResult['error'];
    }
}
}


require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>My Profile</h2>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                        <small class="form-text text-muted">Email cannot be changed</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="qualification" class="form-label">Qualification</label>
                <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo $user['qualification']; ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
            </div>
            <div class="col-md-4">
            <div class="text-center">
                <img src="<?php echo PROFILE_UPLOAD_PATH . $user['profile_image']; ?>" class="img-thumbnail mb-3" alt="Profile Image" style="width: 200px; height: 200px; object-fit: cover;">
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Change Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                </div>
            </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
        
        <hr class="my-4">
        
        <h4>Change Password</h4>
        <form method="post" action="reset_password.php">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>
 <?php
  require_once 'includes/footer.php';
 ?>