
<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>
<div class="hero-section bg-dark text-white py-5 mb-5 rounded">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Find Your Perfect Ride</h1>
                <p class="lead">Discover the best car rental deals for your next adventure</p>
                <a href="cars.php" class="btn btn-primary btn-lg mt-3">Browse Our Fleet</a>
            </div>
            <div class="col-md-6">
                <img src="images/hero-car.png" alt="Car Rental" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<div class="jumbotron bg-light p-5 rounded-lg mb-4">
    <h1 class="display-4">Welcome to CarRental</h1>
    <p class="lead">Find the perfect car for your next adventure at affordable prices.</p>
    <hr class="my-4">
    <p>Browse our wide selection of vehicles and make a reservation today.</p>
    <a class="btn btn-primary btn-lg" href="cars.php" role="button">Browse Cars</a>
</div>

<h2>Featured Cars</h2>
<div class="row">
    <?php
    $stmt = $pdo->query("SELECT * FROM cars WHERE availability = 1 LIMIT 3");
    while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="col-md-4 mb-4">';
        echo '  <div class="card">';
        echo '<img src="' . CAR_UPLOAD_PATH . $car['image_url'] . '" class="card-img-top car-image" alt="' . $car['make'] . ' ' . $car['model'] . '">';
        echo '    <div class="card-body">';
        echo '      <h5 class="card-title">' . $car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')</h5>';
        echo '      <p class="card-text">' . $car['type'] . ' • $' . $car['price_per_day'] . '/day</p>';
        echo '      <p class="card-text"><small class="text-muted">Location: ' . $car['location'] . '</small></p>';
        echo '      <a href="reservation.php?car_id=' . $car['id'] . '" class="btn btn-primary">Rent Now</a>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
    ?>
</div>

<?php
require_once 'includes/footer.php';
?>