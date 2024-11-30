<?php 
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Query to get active promotions for all users (no user_id filtering)
$query = "
    SELECT promo_code, description, discount_percent, start_date, end_date 
    FROM promotions 
    WHERE CURDATE() BETWEEN start_date AND end_date
";

// Prepare the SQL statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit;
}

// Execute the query
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Check if there are any active promotions
$promotions = [];
while ($row = $result->fetch_assoc()) {
    $promotions[] = $row;
}

// Close the statement
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Welcome to Our Booking System</h1>
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
    <section class="hero">
        <div class="hero-content">
            <h1>Your Wellness Journey Starts Here</h1>
            <p>Explore our premium services to enhance your wellbeing.</p>
            <a href="services.php" class="cta-btn">View Services</a>
            <a href="register.php" class="cta-btn">Book Now</a>
        </div>
    </section>

    <!-- Promotions and Rewards Section -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <section class="promotions-rewards">
            <h2>Your Active Promotions</h2>
            <div class="rewards-info">
                <ul>
                    <?php if (empty($promotions)): ?>
                        <p>No active promotions at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($promotions as $promotion): ?>
                            <li>
                                <strong>Promo Code:</strong> <?php echo $promotion['promo_code']; ?><br>
                                <strong>Description:</strong> <?php echo $promotion['description']; ?><br>
                                <strong>Discount:</strong> <?php echo $promotion['discount_percent']; ?>%<br>
                                <strong>Valid From:</strong> <?php echo $promotion['start_date']; ?><br>
                                <strong>Valid Until:</strong> <?php echo $promotion['end_date']; ?><br>
                            </li>
                            <hr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-slider">
            <div class="testimonial-card">
                <img src="./assets/profile.png" alt="Customer Photo" class="testimonial-photo">
                <div class="testimonial-content">
                    <p class="testimonial-rating">⭐⭐⭐⭐⭐</p>
                    <p class="testimonial-comment">"Absolutely loved the service! It was so relaxing and rejuvenating. Highly recommend!"</p>
                    <p class="testimonial-author">John Doe</p>
                </div>
            </div>

            <div class="testimonial-card">
                <img src="./assets/profile1.png" alt="Customer Photo" class="testimonial-photo">
                <div class="testimonial-content">
                    <p class="testimonial-rating">⭐⭐⭐⭐⭐</p>
                    <p class="testimonial-comment">"A wonderful experience. The therapist was professional and made me feel so comfortable!"</p>
                    <p class="testimonial-author">Jane Smith</p>
                </div>
            </div>

            <div class="testimonial-card">
                <img src="./assets/profile.png" alt="Customer Photo" class="testimonial-photo">
                <div class="testimonial-content">
                    <p class="testimonial-rating">⭐⭐⭐⭐⭐</p>
                    <p class="testimonial-comment">"I’ve been to many spas, but this one is by far the best. Will definitely book again!"</p>
                    <p class="testimonial-author">Mark Johnson</p>
                </div>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
