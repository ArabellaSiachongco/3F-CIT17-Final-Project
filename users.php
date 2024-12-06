<?php
session_start();
include 'config.php';

// Fetch current user data if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
}

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Validate and update the user profile
    if (!empty($full_name) && !empty($email) && !empty($phone_number)) {
        $update_query = "UPDATE users SET full_name = ?, email = ?, phone_number = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sssi', $full_name, $email, $phone_number, $user_id);

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile.";
        }
    } else {
        $message = "All fields are required.";
    }
}

// Handle password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password fields
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            // Verify current password
            if (password_verify($current_password, $user_data['password'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_password_query);
                $stmt->bind_param('si', $hashed_new_password, $user_id);

                if ($stmt->execute()) {
                    $password_message = "Password changed successfully!";
                } else {
                    $password_message = "Error changing password.";
                }
            } else {
                $password_message = "Current password is incorrect.";
            }
        } else {
            $password_message = "New password and confirmation do not match.";
        }
    } else {
        $password_message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
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
    background-color: #F4E1D2; /* Light coffee background */
    color: #3E2723; /* Dark coffee text */
}

header {
    background-color: #6D4C41; /* Coffee brown */
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
    background-color: #6D4C41; /* Coffee brown */
    color: #fff;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    width: 100%;
    bottom: 0;
}

main {
    max-width: 1200px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #6D4C41; /* Coffee brown */
    margin-bottom: 20px;
}

h3 {
    color: #6D4C41; /* Coffee brown */
    margin-bottom: 10px;
}

section {
    margin-bottom: 30px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-weight: bold;
    color: #6D4C41; /* Coffee brown */
}

input[type="text"], input[type="email"], input[type="password"] {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s;
}

input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
    border-color: #6D4C41; /* Coffee brown focus border */
    outline: none;
}

button {
    background-color: #6D4C41; /* Coffee brown */
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #5D4037; /* Slightly darker brown on hover */
}

p {
    margin-top: 20px;
    font-size: 16px;
}

/* Message styles */
p.success {
    color: #4CAF50; /* Green for success */
}

p.error {
    color: #D32F2F; /* Red for error */
}

/* Responsive styles */
@media (max-width: 768px) {
    main {
        padding: 15px;
    }

    section {
        margin-bottom: 20px;
    }

    form {
        gap: 10px;
    }
}
</style>
<body>

<header>
    <h1>User Dashboard</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="users.php">Account Settings</a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="login.php">Login</a></li>
            <?php else: ?>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <h2>Account Settings</h2>

    <!-- Profile Update Section -->
    <section>
        <h3>Profile</h3>
        <form action="users.php" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : htmlspecialchars($user_data['full_name']); ?>" required><br>
            
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_data['email']); ?>" required><br>
            
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : htmlspecialchars($user_data['phone_number']); ?>" required><br>
            
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
    </section>

    <!-- Password Change Section -->
    <section>
        <h3>Change Password</h3>
        <form action="users.php" method="POST">
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" required><br>
            
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required><br>
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required><br>
            
            <button type="submit" name="change_password">Change Password</button>
        </form>
        <?php if (isset($password_message)): ?>
            <p style="color: red;"><?php echo $password_message; ?></p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
