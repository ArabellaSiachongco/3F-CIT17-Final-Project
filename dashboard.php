<?php
session_start();
include 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = $conn->prepare("SELECT full_name, email, phone_number FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Fetch appointments
$upcoming_query = $conn->prepare("SELECT * FROM appointments WHERE user_id = ? AND appointment_date >= CURDATE()");
$upcoming_query->bind_param("i", $user_id);
$upcoming_query->execute();
$upcoming_appointments = $upcoming_query->get_result();

$past_query = $conn->prepare("SELECT * FROM appointments WHERE user_id = ? AND appointment_date < CURDATE()");
$past_query->bind_param("i", $user_id);
$past_query->execute();
$past_appointments = $past_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main>
    <section class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($user['phone_number']); ?></p>
    </section>

    <section class="appointments">
        <h2>Upcoming Appointments</h2>
        <?php if ($upcoming_appointments->num_rows > 0): ?>
            <ul>
                <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                    <li>
                        <strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_id']); ?>
                        <strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?>
                        <strong>Time:</strong> <?php echo htmlspecialchars($appointment['start_time']); ?>
                        <button class="cancel-btn" data-id="<?php echo $appointment['appointment_id']; ?>">Cancel</button>
                        <button class="reschedule-btn" data-id="<?php echo $appointment['appointment_id']; ?>">Reschedule</button>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No upcoming appointments.</p>
        <?php endif; ?>

        <h2>Past Appointments</h2>
        <?php if ($past_appointments->num_rows > 0): ?>
            <ul>
                <?php while ($appointment = $past_appointments->fetch_assoc()): ?>
                    <li>
                        <strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_id']); ?>
                        <strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?>
                        <strong>Time:</strong> <?php echo htmlspecialchars($appointment['start_time']); ?>
                        <button class="review-btn" data-id="<?php echo $appointment['appointment_id']; ?>">Leave Review</button>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No past appointments.</p>
        <?php endif; ?>
    </section>

    <section class="account-settings">
        <h2>Account Settings</h2>
        <form action="update_profile.php" method="POST">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
            
            <button type="submit">Update Profile</button>
        </form>

        <form action="change_password.php" method="POST">
            <h3>Change Password</h3>
            <label for="old_password">Old Password</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Change Password</button>
        </form>
    </section>

    <section class="promotions">
        <h2>Promotions & Rewards</h2>
        <p>Coming Soon!</p>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
<script src="scripts.js"></script>
</body>
</html>
