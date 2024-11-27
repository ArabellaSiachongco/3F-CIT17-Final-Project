<?php
session_start();
include 'config.php';

//  filtering and sorting
$service_type = isset($_GET['service_type']) ? $_GET['service_type'] : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'price_asc';

$query = "SELECT * FROM services WHERE 1";
if ($service_type) {
    $query .= " AND service_name = '$service_type'";
}

if ($price_range) {
    $price_limits = explode('-', $price_range);
    $query .= " AND price BETWEEN " . $price_limits[0] . " AND " . $price_limits[1];
}

if ($duration) {
    $query .= " AND duration = $duration";
}

switch ($sort_by) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'duration_asc':
        $query .= " ORDER BY duration ASC";
        break;
    case 'duration_desc':
        $query .= " ORDER BY duration DESC";
        break;
    case 'popularity':
        $query .= " ORDER BY popularity DESC"; 
        break;
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Our Services</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="users.php">Users</a></li>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php else: ?>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <div class="service-filters">
        <form action="services.php" method="GET">
            <div class="filter-group">
                <label for="service_type">Service Type:</label>
                <select name="service_type" id="service_type">
                    <option value="">All</option>
                    <option value="Massage" <?php echo ($service_type == 'Massage') ? 'selected' : ''; ?>>Massage</option>
                    <option value="Facial" <?php echo ($service_type == 'Facial') ? 'selected' : ''; ?>>Facial</option>
                    <option value="Haircut" <?php echo ($service_type == 'Haircut') ? 'selected' : ''; ?>>Haircut</option>
                    <option value="Housekeeping" <?php echo ($service_type == 'Housekeeping') ? 'selected' : ''; ?>>Housekeeping</option>
                    <option value="Maintenance service" <?php echo ($service_type == 'Maintenance service') ? 'selected' : ''; ?>>Maintenance service</option>
                    <option value="Graphic design" <?php echo ($service_type == 'Graphic design') ? 'selected' : ''; ?>>Graphic design</option>
                    <option value="Auto mechanic shop" <?php echo ($service_type == 'Auto mechanic shop') ? 'selected' : ''; ?>>Auto mechanic shop</option>
                    <option value="Car washes" <?php echo ($service_type == 'Car washes') ? 'selected' : ''; ?>>Car washes</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="price_range">Price Range:</label>
                <select name="price_range" id="price_range">
                    <option value="">All</option>
                    <option value="0-50" <?php echo ($price_range == '0-50') ? 'selected' : ''; ?>>Under $50</option>
                    <option value="51-100" <?php echo ($price_range == '51-100') ? 'selected' : ''; ?>>$51 - $100</option>
                    <option value="101-200" <?php echo ($price_range == '101-200') ? 'selected' : ''; ?>>$101 - $200</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="duration">Duration:</label>
                <select name="duration" id="duration">
                    <option value="">All</option>
                    <option value="30" <?php echo ($duration == '30') ? 'selected' : ''; ?>>30 minutes</option>
                    <option value="45" <?php echo ($duration == '45') ? 'selected' : ''; ?>>45 minutes</option>
                    <option value="60" <?php echo ($duration == '60') ? 'selected' : ''; ?>>60 minutes</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort_by">Sort By:</label>
                <select name="sort_by" id="sort_by">
                    <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="duration_asc" <?php echo ($sort_by == 'duration_asc') ? 'selected' : ''; ?>>Duration: Short to Long</option>
                    <option value="duration_desc" <?php echo ($sort_by == 'duration_desc') ? 'selected' : ''; ?>>Duration: Long to Short</option>
                    <option value="popularity" <?php echo ($sort_by == 'popularity') ? 'selected' : ''; ?>>Popularity</option>
                </select>
            </div>

            <button type="submit">Apply Filters</button>
        </form>
    </div>

    <h2>Available Services</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="service-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="service-card">
                    <h3><?php echo $row['service_name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p>Duration: <?php echo $row['duration']; ?> minutes</p>
                    <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                    <a href="booking.php?service_id=<?php echo $row['service_id']; ?>" class="cta-btn">Book Now</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No services available.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
