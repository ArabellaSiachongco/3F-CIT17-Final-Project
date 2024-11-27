<?php
include 'config.php';

$query = "SELECT * FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Users</h1>
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
    <h2>All Users</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="user-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
                    <h3><?php echo $row['full_name']; ?></h3>
                    <p>Email: <?php echo $row['email']; ?></p>
                    <p>Phone: <?php echo $row['phone_number']; ?></p>
                    <p>Role: <?php echo ucfirst($row['role']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Booking System</p>
</footer>

</body>
</html>
