<?php
session_start();
include 'db.php';

$errors = [];

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the user_id and role
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('User not found.');
}

$user = $result->fetch_assoc();
if ($user['role'] !== 'customer') {
    die('Only customers can book appointments.');
}

$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $therapist_id = $_POST['therapist_id']; 
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $payment_method = $_POST['payment_method'];
    $promo_code = $_POST['promo_code'] ?? '';

    // Default discount
    $discount = 0;

    // Validate inputs
    if (empty($appointment_date) || empty($start_time) || empty($end_time) || empty($payment_method)) {
        $errors[] = "Please fill out all the required fields.";
    }

    if (empty($errors)) {
        // Insert into Appointments table
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, therapist_id, service_id, appointment_date, start_time, end_time, promo_code) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissss", $user_id, $therapist_id, $service_id, $appointment_date, $start_time, $end_time, $promo_code);

        if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
        } else {
            $errors[] = "Failed to book the appointment.";
            echo "Error: " . $stmt->error;
            $stmt->close();
            exit;
        }
        $stmt->close();


        //  Promo Code
        if (!empty($promo_code)) {
            $promo_stmt = $conn->prepare("SELECT discount_percent, description FROM promotions WHERE promo_code = ? AND NOW() BETWEEN start_date AND end_date");
            $promo_stmt->bind_param("s", $promo_code);
            $promo_stmt->execute();
            $promo_result = $promo_stmt->get_result();

            if ($promo_result->num_rows > 0) {
                $promo = $promo_result->fetch_assoc();
                $discount = $promo['discount_percent'];
                $description = $promo['description'];
            } else {
                $errors[] = "Invalid or expired promo code.";
            }

            $promo_stmt->close();
        }

        // Calculate?
        $service_stmt = $conn->prepare("SELECT price FROM services WHERE service_id = ?");
        $service_stmt->bind_param("i", $service_id);
        $service_stmt->execute();
        $service_result = $service_stmt->get_result();
        $service_price = 0;

        if ($service_result->num_rows > 0) {
            $service = $service_result->fetch_assoc();
            $service_price = $service['price'];
        }
        $service_stmt->close();

        $final_amount = $service_price - ($service_price * ($discount / 100)); // Apply discount


        if (isset($appointment_id)) {
            $payment_status = 'completed'; 
            $transaction_id = uniqid("txn_"); 
            $payment_date = date("Y-m-d H:i:s"); 

            $payment_stmt = $conn->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id, payment_date) 
                                            VALUES (?, ?, ?, ?, ?, ?)");
            $payment_stmt->bind_param("idssss", $appointment_id, $final_amount, $payment_method, $payment_status, $transaction_id, $payment_date);

            if ($payment_stmt->execute()) {
                echo "Appointment booked successfully!";
            } else {
                $errors[] = "Failed to process the payment.";
                echo "Error: " . $payment_stmt->error; 
            }
            $payment_stmt->close();
        } else {
            $errors[] = "Failed to create an appointment. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
</head>
<style>
/* General Styles */
body {
    font-family: 'Georgia', serif;
    background-color: #f5ebe0; /* Coffee cream */
    margin: 0;
    padding: 0;
}

/* Header Styles */
header {
    background-color: #6f4e37; /* Coffee brown */
    color: #fff7e6; /* Creamy white */
    padding: 15px 0;
    text-align: center;
}

header h1 {
    margin: 0;
    font-size: 2.5em;
    font-weight: bold;
}

/* Navigation Styles */
nav ul {
    list-style: none;
    padding: 0;
    margin: 10px 0 0;
    text-align: center;
}

nav ul li {
    display: inline-block;
    margin: 0 15px;
}

nav ul li a {
    color: #fff7e6;
    text-decoration: none;
    font-weight: bold;
    padding: 5px 15px;
    border-radius: 3px;
    font-size: 1em;
}

nav ul li a:hover {
    background-color: #3e2723; /* Dark espresso */
    color: #ffffff; /* Bright contrast */
}

/* Form Styles */
form {
    max-width: 600px;
    margin: 30px auto;
    background-color: #fff7e6; /* Light cream */
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

form h2 {
    font-size: 1.8em;
    margin-bottom: 15px;
    color: #3e2723; /* Espresso */
}

label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
    color: #6f4e37; /* Coffee brown */
}

input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #d1c4b1; /* Soft coffee border */
    border-radius: 5px;
    font-size: 1em;
    font-family: 'Georgia', serif;
    background-color: #fefcf9; /* Cream background */
}

input:focus, select:focus {
    border-color: #6f4e37; /* Highlight in coffee brown */
    outline: none;
}

button {
    padding: 12px 20px;
    background-color: #6f4e37; /* Coffee brown */
    color: #fff7e6; /* Creamy white */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1em;
    width: 100%;
    font-family: 'Georgia', serif;
}

button:hover {
    background-color: #3e2723; /* Dark espresso */
}

/* Error Messages */
.error-messages {
    background-color: #f8e4d9; /* Light coffee blush */
    color: #8b3a2e; /* Deep coffee red */
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
    font-weight: bold;
}

/* Footer Styles */
footer {
    background-color: #3e2723; /* Dark espresso */
    color: #fff7e6; /* Cream */
    text-align: center;
    padding: 15px;
    margin-top: 30px;
    font-family: 'Georgia', serif;
    font-size: 0.9em;
}
</style>
<body>
<header>
    <h1>Book an Appointment</h1>
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

<form method="POST" action="booking.php">
    <h2>Step 1: Select Service and Therapist</h2>
    <label for="service_id">Select Service:</label>
    <select name="service_id" required>
        <!-- Example services dropdown -->
        <?php
        $service_query = $conn->query("SELECT * FROM services");
        while ($row = $service_query->fetch_assoc()) {
            echo "<option value='{$row['service_id']}'>{$row['service_name']} - $ {$row['price']}</option>";
        }
        ?>
    </select>

    <label for="therapist_id">Select Therapist:</label>
    <select name="therapist_id" required>
        <?php
        $therapist_query = $conn->query("SELECT * FROM users WHERE role = 'therapist'");
        while ($row = $therapist_query->fetch_assoc()) {
            echo "<option value='{$row['user_id']}'>{$row['full_name']}</option>";
        }
        ?>
    </select>

    <h2>Step 2: Choose Date and Time</h2>
    <label for="appointment_date">Date:</label>
    <input type="date" name="appointment_date" required>

    <label for="start_time">Start Time:</label>
    <input type="time" name="start_time" required>

    <label for="end_time">End Time:</label>
    <input type="time" name="end_time" required>

    <h2>Step 3: Confirmation and Payment</h2>
    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" required>
        <option value="cash">Cash</option>
        <option value="credit_card">Credit Card</option>
        <option value="paypal">PayPal</option>
    </select>

    <label for="promo_code">Promo Code (Optional):</label>
    <input type="text" name="promo_code" placeholder="Enter promo code">

    <button type="submit" class="btn">Confirm Appointment</button>
</form>

<!-- Display errors if any -->
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<footer>
    <p>&copy; 2024 Booking System. All rights reserved.</p>
</footer>

<script>
    document.querySelector("input[name='appointment_date']").addEventListener('change', function() {
        var date = this.value;
        var startTime = document.querySelector("input[name='start_time']").value;
        var endTime = document.querySelector("input[name='end_time']").value;
        document.getElementById('appointment-date-time').textContent = date + " " + startTime + " - " + endTime;
    });

    document.querySelector("select[name='therapist_id']").addEventListener('change', function() {
        var therapistName = this.options[this.selectedIndex].text;
        document.getElementById('therapist-name').textContent = therapistName;
    });
</script>
</body>
</html>
