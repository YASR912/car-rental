<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get search parameters
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';

// Build query based on search parameters
$query = "SELECT * FROM cars WHERE availability = 1";
$params = [];

if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($type)) {
    $query .= " AND type = ?";
    $params[] = $type;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">Our Car Fleet</h2>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Search Filters</h5>
        <form method="get" action="cars.php">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>" placeholder="City or area">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="type" class="form-label">Car Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="Sedan" <?php if ($type == 'Sedan') echo 'selected'; ?>>Sedan</option>
                            <option value="SUV" <?php if ($type == 'SUV') echo 'selected'; ?>>SUV</option>
                            <option value="Sports" <?php if ($type == 'Sports') echo 'selected'; ?>>Sports</option>
                            <option value="Electric" <?php if ($type == 'Electric') echo 'selected'; ?>>Electric</option>
                            <option value="Luxury" <?php if ($type == 'Luxury') echo 'selected'; ?>>Luxury</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="cars.php" class="btn btn-secondary">Clear Filters</a>
        </form>
    </div>
</div>

<div class="row">
    <?php if (count($cars) > 0): ?>
        <?php foreach ($cars as $car): ?>
        <div class="col-md-4 mb-4">
            <div class="card car-card h-100">
                <div class="car-image-container">
                    <!-- UPDATED CAR IMAGE DISPLAY -->
                    <img src="<?php echo CAR_UPLOAD_PATH . $car['image_url']; ?>" class="card-img-top car-image" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>">
                    <div class="car-price">$<?php echo $car['price_per_day']; ?>/day</div>
                    <?php if (!empty($start_date) && !empty($end_date)): 
                        $datetime1 = new DateTime($start_date);
                        $datetime2 = new DateTime($end_date);
                        $interval = $datetime1->diff($datetime2);
                        $days = $interval->days;
                        $total_price = $days * $car['price_per_day'];
                    ?>
                    <div class="car-total-price">Total: $<?php echo number_format($total_price, 2); ?></div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')'; ?></h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary"><?php echo $car['type']; ?></span>
                        <span class="text-muted"><i class="fa fa-map-marker"></i> <?php echo $car['location']; ?></span>
                    </div>
                    <p class="card-text"><?php echo $car['description']; ?></p>
                </div>
                <div class="card-footer bg-white">
                    <a href="reservation.php?car_id=<?php echo $car['id']; ?><?php echo !empty($start_date) ? '&start_date='.$start_date : ''; ?><?php echo !empty($end_date) ? '&end_date='.$end_date : ''; ?>" class="btn btn-primary w-100">Rent Now</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                <i class="fa fa-car fa-3x mb-3"></i>
                <h4>No cars available matching your criteria.</h4>
                <p class="mb-0">Try adjusting your search filters or <a href="cars.php">view all cars</a>.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>