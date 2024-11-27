<?php
session_start();
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

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-slider">

            <div class="testimonial-card">
                <img src="customer1.jpg" alt="Customer Photo" class="testimonial-photo">
                <div class="testimonial-content">
                    <p class="testimonial-rating">⭐⭐⭐⭐⭐</p>
                    <p class="testimonial-comment">"Absolutely loved the service! It was so relaxing and rejuvenating. Highly recommend!"</p>
                    <p class="testimonial-author">John Doe</p>
                </div>
            </div>

            <div class="testimonial-card">
                <img src="customer2.jpg" alt="Customer Photo" class="testimonial-photo">
                <div class="testimonial-content">
                    <p class="testimonial-rating">⭐⭐⭐⭐⭐</p>
                    <p class="testimonial-comment">"A wonderful experience. The therapist was professional and made me feel so comfortable!"</p>
                    <p class="testimonial-author">Jane Smith</p>
                </div>
            </div>

            <div class="testimonial-card">
                <img src="customer3.jpg" alt="Customer Photo" class="testimonial-photo">
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
