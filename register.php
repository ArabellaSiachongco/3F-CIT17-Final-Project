<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone_number = mysqli_real_escape_string($conn, trim($_POST['phone_number']));
    $password = trim($_POST['password']); // Plain text password input from the form

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_email_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "An account with this email already exists.";
    } else {
        $query = "INSERT INTO users (full_name, email, phone_number, password, role) VALUES (?, ?, ?, ?, 'customer')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssss', $full_name, $email, $phone_number, $hashed_password);

        if ($stmt->execute()) {
            header('Location: login.php');
            exit;
        } else {
            $error_message = "Registration failed. Please try again.";
        }
    }

    $stmt->close();
}
?>

<!-- HTML Form to Register a User -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Register a New User</h1>
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

<main id="main_form">
    <form action="register.php" method="POST">
        <div>
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required>
        </div>

        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="customer">Customer</option>
                <option value="therapist">Therapist</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div>
            <button type="submit">Register</button>
        </div>
    </form>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
