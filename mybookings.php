
<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user's reservations
$stmt = $pdo->prepare("
    SELECT r.*, c.make, c.model, c.year, c.type, c.image_url 
    FROM reservations r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<h2>My Bookings</h2>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success">
    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<?php if (count($reservations) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Booking Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $reservation['image_url']; ?>" alt="<?php echo $reservation['make'] . ' ' . $reservation['model']; ?>" class="img-thumbnail me-2" style="width: 60px;">
                            <div>
                                <?php echo $reservation['make'] . ' ' . $reservation['model'] . ' (' . $reservation['year'] . ')'; ?><br>
                                <small class="text-muted"><?php echo $reservation['type']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($reservation['pickup_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($reservation['return_date'])); ?></td>
                    <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                            switch($reservation['status']) {
                                case 'pending': echo 'bg-warning'; break;
                                case 'confirmed': echo 'bg-success'; break;
                                case 'completed': echo 'bg-info'; break;
                                case 'cancelled': echo 'bg-danger'; break;
                                default: echo 'bg-secondary';
                            }
                            ?>">
                            <?php echo ucfirst($reservation['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        You haven't made any reservations yet. <a href="cars.php">Browse our cars</a> to get started.
    </div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>