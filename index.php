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
</head>
<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
    color: #333;
}

/* Header */
header {
    background-color: #2c3e50;
    color: white;
    padding: 20px 0;
    text-align: center;
}

header h1 {
    font-size: 2.5em;
    margin: 0;
}

header nav ul {
    list-style-type: none;
    padding: 0;
    margin: 20px 0 0;
}

header nav ul li {
    display: inline;
    margin-right: 15px;
}

header nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 1.1em;
    transition: color 0.3s ease;
}

header nav ul li a:hover {
    color: #3498db;
}

/* Hero Section */
.hero {
    background: url('assets/hero-background.jpg') no-repeat center center/cover;
    color: black;
    padding: 80px 20px;
    text-align: center;
}

.hero-content h1 {
    font-size: 3em;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 1.2em;
    margin-bottom: 30px;
}

.cta-btn {
    background-color: #3498db;
    color: white;
    padding: 15px 30px;
    text-decoration: none;
    font-size: 1.1em;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    margin: 10px;
}

.cta-btn:hover {
    background-color: #2980b9;
}

/* Promotions and Rewards Section */
.promotions-rewards {
    background-color: #fff;
    padding: 40px 20px;
    text-align: center;
}

.promotions-rewards h2 {
    font-size: 2em;
    margin-bottom: 20px;
}

.rewards-info ul {
    list-style-type: none;
    padding: 0;
}

.rewards-info li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
}

.rewards-info hr {
    border: 1px solid #ddd;
    margin: 20px 0;
}

/* Testimonials Section */
.testimonials {
    background-color: #ecf0f1;
    padding: 40px 20px;
    text-align: center;
}

.testimonials h2 {
    font-size: 2.5em;
    margin-bottom: 30px;
}

.testimonial-slider {
    display: flex;
    justify-content: center;
    gap: 20px;
    overflow: hidden;
}

.testimonial-card {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: scale(1.05);
}

.testimonial-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
}

.testimonial-rating {
    font-size: 1.2em;
    margin-bottom: 10px;
}

.testimonial-comment {
    font-style: italic;
    margin-bottom: 10px;
}

.testimonial-author {
    font-weight: bold;
}

/* Footer */
footer {
    background-color: #2c3e50;
    color: white;
    text-align: center;
    padding: 10px 0;
    position: relative;
    bottom: 0;
    width: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.5em;
    }

    .cta-btn {
        padding: 12px 25px;
        font-size: 1em;
    }

    .testimonial-slider {
        flex-direction: column;
        align-items: center;
    }

    .testimonial-card {
        width: 80%;
    }
}
</style>
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
