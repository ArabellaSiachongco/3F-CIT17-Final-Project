<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch completed appointments for the user
$query = "
    SELECT 
        a.appointment_id, 
        a.appointment_date, 
        a.start_time, 
        a.end_time, 
        s.service_name, 
        u.full_name AS therapist_name, 
        r.rating, 
        r.comment 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.therapist_id = u.user_id
    LEFT JOIN reviews r ON a.appointment_id = r.appointment_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.start_time DESC";

// Prepare the SQL statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    // Handle errors if the query preparation fails
    echo "Error preparing statement: " . $conn->error;
    exit;
}

// Bind the parameter for user_id
$stmt->bind_param("i", $user_id);

// Execute the query
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Initialize an empty array for appointments
$appointments = [];

// Fetch all appointments
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Close the statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Appointments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Past Appointments</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="appointments.php">Book Appointment</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Your Completed Appointments</h2>

    <?php if (count($appointments) > 0): ?>
        <ul class="appointments-list">
            <?php foreach ($appointments as $appointment): ?>
                <li class="appointment-item">
                    <strong>Service:</strong> <?= htmlspecialchars($appointment['service_name']) ?><br>
                    <strong>Therapist:</strong> <?= htmlspecialchars($appointment['therapist_name']) ?><br>
                    <strong>Date:</strong> <?= htmlspecialchars($appointment['appointment_date']) ?><br>
                    <strong>Time:</strong> <?= htmlspecialchars($appointment['start_time']) ?> - <?= htmlspecialchars($appointment['end_time']) ?><br>

                    <?php 
                    // Check if the current time is after the appointment's end time
                    $current_time = date("H:i"); // Get the current time in HH:MM format
                    $appointment_end_time = $appointment['end_time']; // The appointment's end time
                    
                    // Only show review form if the current time is after the appointment's end time
                    if ($appointment['rating']): ?>
                        <!-- Show the existing review if the user has already left a rating -->
                        <strong>Rating:</strong> <?= htmlspecialchars($appointment['rating']) ?>/5<br>
                        <strong>Review:</strong> <?= htmlspecialchars($appointment['comment']) ?><br>
                    <?php else: ?>
                        <!-- Display the form to leave a review only if no review exists and the appointment is completed -->
                        <?php if (strtotime($current_time) > strtotime($appointment_end_time) || $appointment['appointment_date'] < date('Y-m-d')): ?>
                            <!-- Form is only shown if the current time is past the appointment's end time -->
                            <form action="leave_review.php" method="POST">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                <label for="rating_<?= $appointment['appointment_id'] ?>">Rating (1-5):</label>
                                <select name="rating" id="rating_<?= $appointment['appointment_id'] ?>" required>
                                    <option value="">Select</option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <br>
                                <label for="comment_<?= $appointment['appointment_id'] ?>">Comment:</label>
                                <textarea name="comment" id="comment_<?= $appointment['appointment_id'] ?>" rows="3" required></textarea>
                                <br>
                                <button type="submit">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <!-- If the appointment is still ongoing, show a message instead -->
                            <p>You cannot leave a review until the appointment has ended.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no completed appointments yet.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
