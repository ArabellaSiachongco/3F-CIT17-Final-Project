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
</head>
<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Header & Footer */
header, footer {
    background-color: #333;
    color: white;
    text-align: center;
    padding: 15px;
}

footer p {
    margin: 0;
}

/* Main Layout */
main {
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Sections */
section {
    margin-bottom: 30px;
}

/* User Info */
.user-info {
    background-color: #eef2f7;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.user-info h2 {
    margin: 0;
    font-size: 1.8em;
}

.user-info p {
    font-size: 1.1em;
    margin-top: 10px;
}

/* Appointments */
.appointments h2 {
    font-size: 1.7em;
    margin-bottom: 10px;
}

.appointments ul {
    list-style: none;
    padding: 0;
}

.appointments li {
    padding: 10px;
    background-color: #f9f9f9;
    margin-bottom: 10px;
    border-radius: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.appointments strong {
    font-weight: bold;
}

button {
    padding: 8px 15px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 1em;
    margin-left: 10px;
}

button:hover {
    background-color: #2980b9;
}

.cancel-btn {
    background-color: #e74c3c;
}

.cancel-btn:hover {
    background-color: #c0392b;
}

.reschedule-btn {
    background-color: #f39c12;
}

.reschedule-btn:hover {
    background-color: #e67e22;
}

.review-btn {
    background-color: #2ecc71;
}

.review-btn:hover {
    background-color: #27ae60;
}

/* Account Settings */
.account-settings form {
    background-color: #eef2f7;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
}

.account-settings label {
    display: block;
    font-size: 1.1em;
    margin-top: 10px;
}

.account-settings input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 3px;
    border: 1px solid #ccc;
    font-size: 1em;
}

.account-settings button {
    width: 100%;
    padding: 12px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 1.2em;
    margin-top: 20px;
}

.account-settings button:hover {
    background-color: #2980b9;
}

/* Password Change Section */
.account-settings h3 {
    font-size: 1.5em;
    margin-top: 30px;
}

.account-settings input[type="password"] {
    margin-bottom: 15px;
}

/* Promotions Section */
.promotions {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
}

.promotions h2 {
    font-size: 1.7em;
    margin-bottom: 10px;
}

.promotions p {
    font-size: 1.2em;
    color: #888;
}

/* Responsive Design */
@media (max-width: 768px) {
    main {
        padding: 15px;
        margin: 10px;
    }

    .appointments li {
        flex-direction: column;
        align-items: flex-start;
    }

    button {
        width: 100%;
        margin-top: 10px;
    }
}

</style>
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
