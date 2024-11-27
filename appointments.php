<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You need to log in to book an appointment.";
    exit;
}

$user_id = $_SESSION['user_id']; 

$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

$therapists_query = "SELECT user_id, full_name FROM users WHERE role = 'therapist'";
$therapists_result = $conn->query($therapists_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $therapist_id = $_POST['therapist_id']; 
    $service_id = $_POST['service_id']; 
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($therapist_id) || empty($service_id) || empty($appointment_date) || empty($start_time) || empty($end_time)) {
        echo "Please fill all fields.";
        exit;
    }

    $insert_query = "INSERT INTO appointments (user_id, therapist_id, service_id, appointment_date, start_time, end_time, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiisss", $user_id, $therapist_id, $service_id, $appointment_date, $start_time, $end_time);

    if ($stmt->execute()) {
        echo "Appointment booked successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Book Your Appointment</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="users.php">Users</a></li>
            
            <!-- Only show login/register/logout if the user is not logged in -->
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
    <h2>Select a Service and Therapist</h2>

    <form action="appointments.php" method="POST">
        <!-- Service Selection -->
        <div>
            <label for="service_id">Select Service:</label>
            <select name="service_id" id="service_id" required>
                <?php
                // Loop through services 
                while ($service = $services_result->fetch_assoc()) {
                    echo "<option value='" . $service['service_id'] . "'>" . $service['service_name'] . " - $" . number_format($service['price'], 2) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Therapist Selection -->
        <div>
            <label for="therapist_id">Select Therapist:</label>
            <select name="therapist_id" id="therapist_id" required>
                <?php
                // Loop through therapists
                while ($therapist = $therapists_result->fetch_assoc()) {
                    echo "<option value='" . $therapist['user_id'] . "'>" . $therapist['full_name'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Appointment Date and Time -->
        <div>
            <label for="appointment_date">Appointment Date:</label>
            <input type="date" id="appointment_date" name="appointment_date" required>
        </div>

        <div>
            <label for="start_time">Start Time:</label>
            <input type="time" id="start_time" name="start_time" required>
        </div>

        <div>
            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" required>
        </div>

        <div>
            <button type="submit">Book Appointment</button>
        </div>
    </form>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
