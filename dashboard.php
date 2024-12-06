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
/* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f9f3e5; /* Light cream background */
    margin: 0;
    padding: 0;
    line-height: 1.6;
    color: #4b3c2d; /* Dark brown text */
}

/* Header & Footer */
header, footer {
    background-color: #6f4f2f; /* Coffee brown */
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
    background-color: #fff; /* White card background */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Sections */
section {
    margin-bottom: 30px;
}

/* User Info */
.user-info {
    background-color: #fdf9f3; /* Light cream */
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.user-info h2 {
    margin: 0;
    font-size: 2rem;
    color: #6f4f2f;
}

.user-info p {
    font-size: 1.2rem;
    margin-top: 10px;
}

/* Appointments */
.appointments h2 {
    font-size: 1.7rem;
    margin-bottom: 15px;
    color: #6f4f2f;
}

.appointments ul {
    list-style: none;
    padding: 0;
}

.appointments li {
    padding: 15px;
    background-color: #fdf9f3; /* Light cream */
    margin-bottom: 15px;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.appointments strong {
    font-weight: bold;
    color: #4b3c2d;
}

/* Buttons */
button {
    padding: 10px 15px;
    background-color: #6f4f2f; /* Coffee brown */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    margin-left: 10px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

button:hover {
    background-color: #4b3c2d; /* Darker brown on hover */
    transform: scale(1.05);
}

.cancel-btn {
    background-color: #e74c3c; /* Red for cancel */
}

.cancel-btn:hover {
    background-color: #c0392b;
}

.reschedule-btn {
    background-color: #f39c12; /* Amber for reschedule */
}

.reschedule-btn:hover {
    background-color: #e67e22;
}

.review-btn {
    background-color: #2ecc71; /* Green for review */
}

.review-btn:hover {
    background-color: #27ae60;
}

/* Account Settings */
.account-settings form {
    background-color: #fdf9f3; /* Light cream */
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.account-settings label {
    display: block;
    font-size: 1.1rem;
    margin-top: 10px;
    color: #4b3c2d;
}

.account-settings input {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1rem;
    background-color: #fff;
    color: #4b3c2d;
}

.account-settings button {
    width: 100%;
    padding: 12px;
    background-color: #6f4f2f; /* Coffee brown */
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1.2rem;
    margin-top: 20px;
}

.account-settings button:hover {
    background-color: #4b3c2d;
}

/* Password Change Section */
.account-settings h3 {
    font-size: 1.5rem;
    margin-top: 30px;
    color: #6f4f2f;
}

/* Promotions Section */
.promotions {
    background-color: #fdf9f3; /* Light cream */
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.promotions h2 {
    font-size: 1.7rem;
    margin-bottom: 10px;
    color: #6f4f2f;
}

.promotions p {
    font-size: 1.2rem;
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
