
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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    
    // Validation
    if (empty($current_password)) $errors[] = "Current password is required";
    if (empty($new_password)) $errors[] = "New password is required";
    if (strlen($new_password) < 6) $errors[] = "New password must be at least 6 characters";
    if ($new_password !== $confirm_new_password) $errors[] = "New passwords do not match";
    
    // Verify current password
    if (empty($errors) && !password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            $success = "Password changed successfully!";
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Change Password</h2>
        
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
        
        <form method="post">
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
            <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
        </form>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>