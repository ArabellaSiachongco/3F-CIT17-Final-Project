<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle the rescheduling of an appointment (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reschedule') {
    $appointment_id = $_POST['appointment_id'];
    $new_date = $_POST['new_date'];
    $new_start_time = $_POST['new_start_time'];
    $new_end_time = $_POST['new_end_time'];

    // Validate the data
    if (empty($appointment_id) || empty($new_date) || empty($new_start_time) || empty($new_end_time)) {
        $error_message = 'All fields are required.';
    } else {
        // Prepare the SQL query to update the appointment
        $update_query = "UPDATE appointments SET appointment_date = ?, start_time = ?, end_time = ? WHERE appointment_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('sssi', $new_date, $new_start_time, $new_end_time, $appointment_id);

        if ($update_stmt->execute()) {
            $success_message = 'Appointment rescheduled successfully.';
        } else {
            $error_message = 'Failed to reschedule the appointment.';
        }
    }
}

// Fetch upcoming appointments (filter out past ones)
$upcoming_query = "SELECT a.appointment_id, s.service_name, t.full_name AS therapist_name, a.appointment_date, a.start_time, a.end_time 
                   FROM appointments a
                   JOIN services s ON a.service_id = s.service_id
                   JOIN users t ON a.therapist_id = t.user_id
                   WHERE a.user_id = ? AND a.appointment_date >= CURDATE() AND 
                         (a.appointment_date > CURDATE() OR (a.appointment_date = CURDATE() AND a.end_time > CURTIME()))
                   ORDER BY a.appointment_date ASC";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $user_id);
$upcoming_stmt->execute();
$upcoming_appointments = $upcoming_stmt->get_result();

// Fetch past appointments and check if a review exists for each
$past_query = "SELECT a.appointment_id, s.service_name, t.full_name AS therapist_name, a.appointment_date, a.start_time, a.end_time, 
                      r.rating, r.comment
               FROM appointments a
               JOIN services s ON a.service_id = s.service_id
               JOIN users t ON a.therapist_id = t.user_id
               LEFT JOIN reviews r ON a.appointment_id = r.appointment_id
               WHERE a.user_id = ? AND (a.appointment_date < CURDATE() OR (a.appointment_date = CURDATE() AND a.end_time <= CURTIME()))
               ORDER BY a.appointment_date DESC";
$past_stmt = $conn->prepare($past_query);
$past_stmt->bind_param("i", $user_id);
$past_stmt->execute();
$past_appointments = $past_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Your Appointments</h1>
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
    <?php if (isset($success_message)): ?>
        <p style="color: green;"><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <h2>Upcoming Appointments</h2>
    <?php if ($upcoming_appointments->num_rows > 0): ?>
        <ul>
            <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                <li>
                    <strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?><br>
                    <strong>Therapist:</strong> <?php echo htmlspecialchars($appointment['therapist_name']); ?><br>
                    <strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?><br>
                    <strong>Time:</strong> <?php echo htmlspecialchars($appointment['start_time'] . " - " . $appointment['end_time']); ?><br>
                    
                    <!-- Cancel Button -->
                    <button class="cancel-btn" data-id="<?php echo $appointment['appointment_id']; ?>">Cancel</button>
                    
                    <!-- Reschedule Button -->
                    <button class="reschedule-btn" data-id="<?php echo $appointment['appointment_id']; ?>">Reschedule</button>
                    
                    <!-- Reschedule Form (Initially Hidden) -->
                    <div class="reschedule-form" id="reschedule-form-<?php echo $appointment['appointment_id']; ?>" style="display: none;">
                        <form action="appointments.php" method="POST">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                            <input type="hidden" name="action" value="reschedule">
                            
                            <label for="new_date">New Date:</label>
                            <input type="date" name="new_date" required><br>
                            <label for="new_start_time">New Start Time:</label>
                            <input type="time" name="new_start_time" required><br>
                            <label for="new_end_time">New End Time:</label>
                            <input type="time" name="new_end_time" required><br>
                            
                            <button type="submit">Reschedule</button>
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No upcoming appointments found.</p>
    <?php endif; ?>

    <h2>Past Appointments</h2>
    <?php if ($past_appointments->num_rows > 0): ?>
        <ul>
            <?php while ($appointment = $past_appointments->fetch_assoc()): ?>
                <li>
                    <strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?><br>
                    <strong>Therapist:</strong> <?php echo htmlspecialchars($appointment['therapist_name']); ?><br>
                    <strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?><br>
                    <strong>Time:</strong> <?php echo htmlspecialchars($appointment['start_time'] . " - " . $appointment['end_time']); ?><br>

                    <?php if ($appointment['rating']): ?>
                        <strong>Rating:</strong> <?php echo htmlspecialchars($appointment['rating']); ?>/5<br>
                        <strong>Review:</strong> <?php echo htmlspecialchars($appointment['comment']); ?><br>
                    <?php else: ?>
                        <a href="leave_review.php?appointment_id=<?php echo $appointment['appointment_id']; ?>">
                            <button>Leave a Review</button>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No past appointments found.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle reschedule button
    document.querySelectorAll('.reschedule-btn').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');
            const form = document.getElementById('reschedule-form-' + appointmentId);
            
            // Show the reschedule form
            form.style.display = 'block';
        });
    });

    // Handle cancel button (optional logic)
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');
            alert('You clicked cancel for appointment ' + appointmentId);
            // You can implement cancel logic here if needed.
        });
    });
});
</script>

</body>
</html>
