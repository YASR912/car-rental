

<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

// Get car details
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND availability = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    die("Car not found or not available");
}

$errors = [];
$pickup_date = $return_date = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_date = sanitizeInput($_POST['pickup_date']);
    $return_date = sanitizeInput($_POST['return_date']);
    
    // Validation
    if (empty($pickup_date)) $errors[] = "Pickup date is required";
    if (empty($return_date)) $errors[] = "Return date is required";
    
    if (!empty($pickup_date) && !empty($return_date)) {
        $today = date('Y-m-d');
        if ($pickup_date < $today) $errors[] = "Pickup date cannot be in the past";
        if ($return_date <= $pickup_date) $errors[] = "Return date must be after pickup date";
    }
    
    if (empty($errors)) {
        // Calculate total price
        $datetime1 = new DateTime($pickup_date);
        $datetime2 = new DateTime($return_date);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->days;
        $total_price = $days * $car['price_per_day'];
        
        // Create reservation
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, car_id, pickup_date, return_date, total_price) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $car_id, $pickup_date, $return_date, $total_price])) {
            // Update car availability
            $stmt = $pdo->prepare("UPDATE cars SET availability = 0 WHERE id = ?");
            $stmt->execute([$car_id]);
            
            $_SESSION['success'] = "Reservation successful!";
            header("Location: mybookings.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Make Reservation</h2>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="<?php echo $car['image_url']; ?>" class="img-fluid rounded-start" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')'; ?></h5>
                        <p class="card-text">Type: <?php echo $car['type']; ?></p>
                        <p class="card-text">Price: $<?php echo $car['price_per_day']; ?>/day</p>
                        <p class="card-text">Location: <?php echo $car['location']; ?></p>
                        <p class="card-text"><?php echo $car['description']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" class="form-control" id="pickup_date" name="pickup_date" value="<?php echo $pickup_date; ?>" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" class="form-control" id="return_date" name="return_date" value="<?php echo $return_date; ?>" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
            </div>
            
            <?php if (!empty($pickup_date) && !empty($return_date) && $return_date > $pickup_date): 
                $datetime1 = new DateTime($pickup_date);
                $datetime2 = new DateTime($return_date);
                $interval = $datetime1->diff($datetime2);
                $days = $interval->days;
                $total_price = $days * $car['price_per_day'];
            ?>
            <div class="alert alert-info">
                Rental Period: <?php echo $days; ?> days<br>
                Total Price: $<?php echo number_format($total_price, 2); ?>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Confirm Reservation</button>
            <a href="cars.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script>
// Calculate and display price when dates change
document.getElementById('pickup_date').addEventListener('change', calculatePrice);
document.getElementById('return_date').addEventListener('change', calculatePrice);

function calculatePrice() {
    const pickupDate = new Date(document.getElementById('pickup_date').value);
    const returnDate = new Date(document.getElementById('return_date').value);
    
    if (pickupDate && returnDate && returnDate > pickupDate) {
        const days = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
        const pricePerDay = <?php echo $car['price_per_day']; ?>;
        const totalPrice = days * pricePerDay;
        
        // You could display this in a div or alert, but we're already showing it in PHP
        // This would need a more advanced implementation with AJAX to update without refresh
        location.reload(); // Simple solution: reload to recalculate with PHP
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>