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
</head>
<style>
/* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    color: #4e3629; /* Coffee brown text */
    background-color: #f1e4d8; /* Light coffee background */
}

header {
    background-color: #3e2723; /* Dark brown header */
    color: #fff;
    padding: 20px 0;
    text-align: center;
}

header h1 {
    margin-bottom: 10px;
    font-size: 2rem;
}

nav ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

nav ul li {
    display: inline;
    margin-right: 15px;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
}

nav ul li a:hover {
    text-decoration: underline;
}

footer {
    background-color: #3e2723; /* Dark brown footer */
    color: #fff;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    width: 100%;
    bottom: 0;
}

main {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

h2 {
    color: #3e2723; /* Dark brown */
    margin-bottom: 20px;
    font-size: 1.8rem;
}

h3 {
    color: #3e2723; /* Dark brown */
}

.service-filters {
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    background-color: #f8f0e3; /* Light coffee background */
}

.filter-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #3e2723; /* Dark brown text for labels */
}

select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

select:focus {
    border-color: #3e2723; /* Dark brown focus border */
    outline: none;
}

button {
    background-color: #3e2723; /* Dark brown button */
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #4e3629; /* Lighter brown on hover */
}

.service-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.service-card {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s;
}

.service-card:hover {
    transform: translateY(-5px);
}

.service-card h3 {
    color: #3e2723; /* Dark brown */
    margin-bottom: 10px;
}

.service-card p {
    color: #4e3629; /* Slightly lighter coffee brown */
    margin-bottom: 10px;
}

.cta-btn {
    display: inline-block;
    background-color: #3e2723; /* Dark brown */
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 16px;
    transition: background-color 0.3s;
}

.cta-btn:hover {
    background-color: #4e3629; /* Lighter brown on hover */
}

p {
    margin-top: 20px;
}

/* Responsive styles */
@media (max-width: 768px) {
    .service-list {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .service-list {
        grid-template-columns: 1fr;
    }
}

</style>
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
