<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$appointment_id = $_GET['appointment_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $appointment_id = $_POST['appointment_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Validate inputs
    if (empty($appointment_id) || empty($rating) || empty($comment)) {
        echo "All fields are required.";
        exit;
    }

    // Insert the review into the database
    $query = "INSERT INTO reviews (appointment_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $appointment_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
        header("Location: past_appointments.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch the appointment details to display
if ($appointment_id) {
    $query = "SELECT a.appointment_id, s.service_name, t.full_name AS therapist_name
              FROM appointments a
              JOIN services s ON a.service_id = s.service_id
              JOIN users t ON a.therapist_id = t.user_id
              WHERE a.appointment_id = ? AND a.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
} else {
    // If no appointment ID passed, redirect to past appointments
    header("Location: past_appointments.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review</title>
</head>
<style>
/* Global Styles */
/* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif; /* Modern font for a clean look */
    background-color: #f7e8d5;  /* Light beige for a coffee-inspired backdrop */
    color: #4a3625;  /* Rich brown for text */
    line-height: 1.6;
}

header {
    background-color: #5c3d2e;  /* Warm coffee brown */
    color: #fff;
    padding: 15px 0;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

header h1 {
    font-size: 2em;
    margin-bottom: 5px;
}

nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 15px;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
    font-size: 1em;
    padding: 5px 10px;
    transition: color 0.3s ease, background 0.3s ease;
}

nav ul li a:hover {
    background-color: #7d5742;  /* Lighter coffee brown hover */
    border-radius: 5px;
}

footer {
    background-color: #4a3625;  /* Darker brown */
    color: #fff;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    bottom: 0;
    width: 100%;
    font-size: 0.9em;
}

/* Main Content */
main {
    background-color: #fff;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #5c3d2e; /* Coffee brown */
    margin-bottom: 15px;
    font-size: 1.8em;
    text-align: center;
}

h3 {
    color: #4a3625;
    font-size: 1.5em;
    margin-bottom: 10px;
}

p {
    font-size: 1em;
    margin: 10px 0;
}

label {
    font-size: 1em;
    margin-bottom: 5px;
    color: #4a3625;
}

select, textarea, button {
    width: 100%;
    padding: 10px;
    font-size: 1em;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

select {
    background-color: #f9f1e6; /* Soft cream background */
}

textarea {
    resize: vertical;
    background-color: #f9f1e6;
}

select:focus, textarea:focus, button:focus {
    border-color: #5c3d2e;
    outline: none;
    background-color: #f4e5d3; /* Subtle highlight */
}

button {
    background-color: #5c3d2e;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

button:hover {
    background-color: #7d5742; /* Lighter brown */
    transform: scale(1.02); /* Slight pop effect */
}

button:active {
    transform: scale(0.98); /* Subtle click effect */
}

/* Responsive Design */
@media (max-width: 768px) {
    main {
        margin: 10px;
        padding: 15px;
    }

    header h1 {
        font-size: 1.8em;
    }

    nav ul {
        flex-wrap: wrap;
        gap: 10px;
    }
}

</style>
<body>

<header>
    <h1>Leave a Review for Appointment</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="past_appointments.php">Past Appointments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Review Appointment</h2>

    <?php if ($appointment): ?>
        <h3>Appointment Details</h3>
        <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?></p>
        <p><strong>Therapist:</strong> <?php echo htmlspecialchars($appointment['therapist_name']); ?></p>

        <!-- Review Form -->
        <form action="leave_review.php" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">

            <label for="rating">Rating (1-5):</label>
            <select name="rating" id="rating" required>
                <option value="" disabled selected>Select rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>

            <label for="comment">Comment:</label>
            <textarea name="comment" id="comment" rows="4" required></textarea>

            <button type="submit">Submit Review</button>
        </form>

    <?php else: ?>
        <p>Appointment not found. Please make sure you've selected a valid appointment.</p>
    <?php endif; ?>
</main>
<!-- 
<footer>
    <p>&copy; 2024 Booking System</p>
</footer> -->

</body>
</html>
