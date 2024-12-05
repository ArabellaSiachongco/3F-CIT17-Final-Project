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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    background-color: #f9f3e5;  /* Light cream background */
    color: #4b3c2d;  /* Dark brown text */
}

header {
    background-color: #6f4f2f;  /* Coffee brown */
    color: #fff;
    padding: 20px 0;
    text-align: center;
}

header h1 {
    margin-bottom: 10px;
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
    background-color: #4b3c2d;  /* Dark brown footer */
    color: #fff;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    width: 100%;
    bottom: 0;
}

main {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

h2 {
    color: #6f4f2f;  /* Coffee brown */
}

h3 {
    color: #4b3c2d;  /* Dark brown */
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

select, textarea, button {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

select:focus, textarea:focus, button:focus {
    border-color: #6f4f2f;  /* Dark brown focus border */
    outline: none;
}

button {
    background-color: #6f4f2f;  /* Coffee brown */
    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #4b3c2d;  /* Darker brown on hover */
}

textarea {
    resize: vertical;
}

p {
    margin-top: 15px;
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
