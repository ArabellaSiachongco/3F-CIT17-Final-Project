<?php
session_start();
require_once 'db.php'; // Include database connection

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle actions like reschedule or cancel bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
        $action = $_POST['action'];
        $appointment_id = intval($_POST['appointment_id']);

        if ($action === 'cancel') {
            $sql = "DELETE FROM appointments WHERE appointment_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $appointment_id);
            if ($stmt->execute()) {
                $success_message = 'Appointment cancelled successfully.';
            } else {
                $error_message = 'Failed to cancel appointment.';
            }
            $stmt->close();
        } elseif ($action === 'reschedule') {
            $new_date = $_POST['new_date'];
            $new_time = $_POST['new_time'];
            $sql = "UPDATE appointments SET appointment_date = ?, start_time = ? WHERE appointment_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $new_date, $new_time, $appointment_id);
            if ($stmt->execute()) {
                $success_message = 'Appointment rescheduled successfully.';
            } else {
                $error_message = 'Failed to reschedule appointment.';
            }
            $stmt->close();
        }
    }
}

// Handle adding new availability for therapists
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_availability'])) {
    $therapist_id = $_POST['therapist_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($therapist_id) || empty($start_time) || empty($end_time)) {
        $error_message = 'All fields are required.';
    } else {
        $insert_query = "INSERT INTO therapist_availability (therapist_id, start_time, end_time) 
                         VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('iss', $therapist_id, $start_time, $end_time);

        if ($insert_stmt->execute()) {
            $success_message = 'Therapist availability added successfully.';
        } else {
            $error_message = 'Failed to add availability.';
        }
    }
}

// Fetch therapist availability for the calendar view
$availability_query = "SELECT * FROM therapist_availability ORDER BY start_time";
$availability_result = $conn->query($availability_query);

// Fetch all therapists
$therapists_query = "SELECT * FROM users WHERE role = 'therapist'";
$therapists_result = $conn->query($therapists_query);

// Fetch bookings for the admin dashboard
$sql = "SELECT 
    a.appointment_id, 
    u.full_name AS customer_name, 
    t.full_name AS therapist_name, 
    s.service_name, 
    a.appointment_date, 
    a.start_time, 
    a.end_time 
FROM appointments a
JOIN users u ON a.user_id = u.user_id
JOIN users t ON a.therapist_id = t.user_id
JOIN services s ON a.service_id = s.service_id
ORDER BY a.appointment_date DESC";

$result = $conn->query($sql);

// Fetch all services for the manage services section
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

// Fetch payments data
$filter = 'paid'; // Default filter
if (isset($_GET['status'])) {
    $filter = $_GET['status'];  // Paid, Unpaid, or Refunded
}

$payments_query = "SELECT p.payment_id, p.amount, p.payment_status AS status, p.payment_date, a.appointment_date, u.full_name AS customer_name
                   FROM payments p
                   JOIN appointments a ON p.appointment_id = a.appointment_id
                   JOIN users u ON a.user_id = u.user_id
                   WHERE p.payment_status = ?
                   ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($payments_query);
$stmt->bind_param('s', $filter);
$stmt->execute();
$payments_result = $stmt->get_result();

// Fetch total earnings per month
$earnings_query = "SELECT MONTH(payment_date) AS month, SUM(amount) AS total_earnings
                   FROM payments
                   WHERE payment_status = 'paid'
                   GROUP BY MONTH(payment_date)
                   ORDER BY month";
$earnings_result = $conn->query($earnings_query);

// Fetch total bookings per month
$bookings_query = "SELECT MONTH(appointment_date) AS month, COUNT(*) AS total_bookings
                   FROM appointments
                   GROUP BY MONTH(appointment_date)
                   ORDER BY month";
$bookings_result = $conn->query($bookings_query);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background-color: #f7f0e6; /* Coffee cream background */
            color: #4e342e; /* Coffee brown text */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #3e2723; /* Dark coffee brown */
        }

        nav {
            margin: 20px 0;
            background-color: #d7ccc8; /* Light mocha */
            padding: 10px;
            border-radius: 5px;
        }

        nav a {
            margin-right: 15px;
            color: #5d4037; /* Medium coffee brown */
            text-decoration: none;
        }

        nav a:hover {
            text-decoration: underline;
            color: #3e2723; /* Dark coffee brown on hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #d7ccc8; /* Light mocha borders */
            text-align: left;
        }

        th {
            background-color: #6d4c41; /* Rich coffee brown */
            color: #ffffff; /* White text */
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success {
            background-color: #d7ffd9; /* Light green for success */
            color: #2e7d32; /* Dark green text */
        }

        .error {
            background-color: #ffebee; /* Light red for errors */
            color: #c62828; /* Dark red text */
        }

        form input, form select, form textarea, form button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d7ccc8; /* Light mocha border */
            border-radius: 5px;
            background-color: #f7f0e6; /* Coffee cream background */
            color: #4e342e; /* Coffee brown text */
        }

        form button {
            background-color: #6d4c41; /* Rich coffee brown */
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        form button:hover {
            background-color: #5d4037; /* Slightly darker brown */
        }

        .calendar {
            display: flex;
            flex-wrap: wrap;
        }

        .calendar-day {
            width: 200px;
            height: 150px;
            border: 1px solid #ccc;
            text-align: center;
            padding: 10px;
            margin: 10px;
            background-color: #ffe0b2; /* Light coffee foam */
        }

        .calendar-day p {
            margin: 5px 0;
            color: #4e342e; /* Coffee brown text */
        }

        .chart-container {
            margin-top: 40px;
        }

        /* Add shadows and rounded corners for a more modern touch */
        table, .calendar-day, form button, nav {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">All Bookings</a> | 
        <a href="admin_dashboard.php?section=services">Manage Services</a> |
        <a href="admin_dashboard.php?section=availability">Therapist Schedule</a> |
        <a href="admin_dashboard.php?section=payments">Payments</a> |
        <a href="admin_dashboard.php?section=reports">Reports</a>
    </nav>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!isset($_GET['section']) || $_GET['section'] === 'appointments'): ?>

        <!-- Booking Management Section -->
        <h2>All Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Therapist</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['appointment_id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['therapist_name']) ?></td>
                            <td><?= htmlspecialchars($row['service_name']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($row['start_time']) ?></td>
                            <td><?= htmlspecialchars($row['end_time']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                    <button type="submit" name="action" value="cancel">Cancel</button>
                                    <input type="date" name="new_date">
                                    <input type="time" name="new_time">
                                    <button type="submit" name="action" value="reschedule">Reschedule</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($_GET['section'] === 'services'): ?>

        <!-- Manage Services Section -->
        <h2>Manage Services</h2>
        <h3>Add New Service</h3>
        <form method="POST" action="admin_dashboard.php">
            <input type="text" name="service_name" placeholder="Service Name" required><br>
            <textarea name="description" placeholder="Service Description" required></textarea><br>
            <input type="number" name="price" placeholder="Price" step="0.01" required><br>
            <input type="number" name="duration" placeholder="Duration (minutes)" required><br>
            <button type="submit" name="add_service">Add Service</button>
        </form>

        <hr>

        <!-- Service List -->
        <h3>Service List</h3>
        <?php if ($services_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($service = $services_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                            <td>$<?php echo htmlspecialchars($service['price']); ?></td>
                            <td><?php echo htmlspecialchars($service['duration']); ?> mins</td>
                            <td>
                                <a href="edit_service.php?service_id=<?php echo $service['service_id']; ?>">Edit</a> |
                                <a href="admin_dashboard.php?delete_service_id=<?php echo $service['service_id']; ?>" onclick="return confirm('Are you sure you want to delete this service?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No services found.</p>
        <?php endif; ?>
    <?php elseif ($_GET['section'] === 'availability'): ?>

        <?php
// Fetch therapist availability with the therapist's name
$availability_query = "
    SELECT ta.*, u.full_name AS therapist_name
    FROM therapist_availability ta
    JOIN users u ON ta.therapist_id = u.user_id
    ORDER BY ta.start_time";
$availability_result = $conn->query($availability_query);
?>

<!-- Therapist Schedule Management Section -->
<h2>Manage Therapist Availability</h2>

<!-- Add Availability Form -->
<h3>Add Availability</h3>
<form method="POST" action="admin_dashboard.php?section=availability">
    <!-- Therapist Dropdown -->
    <select name="therapist_id" required>
        <option value="">Select Therapist</option>
        <?php while ($therapist = $therapists_result->fetch_assoc()): ?>
            <option value="<?= $therapist['user_id'] ?>"><?= htmlspecialchars($therapist['full_name']) ?></option>
        <?php endwhile; ?>
    </select><br>

    <!-- Start Time -->
    <input type="datetime-local" name="start_time" required><br>

    <!-- End Time -->
    <input type="datetime-local" name="end_time" required><br>

    <!-- Submit Button -->
    <button type="submit" name="add_availability">Add Availability</button>
</form>

<hr>

<!-- Therapist Availability Calendar -->
<h3>Therapist Availability Calendar</h3>
<div class="calendar">
    <?php if ($availability_result->num_rows > 0): ?>
        <?php while ($availability = $availability_result->fetch_assoc()): ?>
            <div class="calendar-day">
                <!-- Display Therapist's Name -->
                <p>Therapist: <?= htmlspecialchars($availability['therapist_name']) ?></p>
                <!-- Display Start Time -->
                <p>From: <?= htmlspecialchars($availability['start_time']) ?></p>
                <!-- Display End Time -->
                <p>To: <?= htmlspecialchars($availability['end_time']) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No availability scheduled yet.</p>
    <?php endif; ?>
</div>

    <?php elseif ($_GET['section'] === 'payments'): ?>

        <h2>Payments</h2>
        <form method="GET" action="admin_dashboard.php?section=payments">
            <label for="status">Filter by Status: </label>
            <select name="status" id="status">
                <option value="paid" <?= $filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="unpaid" <?= $filter === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="refunded" <?= $filter === 'refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
            <button type="submit">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                    <th>Appointment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments_result->num_rows > 0): ?>
                    <?php while ($payment = $payments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                            <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                            <td>$<?= htmlspecialchars($payment['amount']) ?></td>
                            <td><?= htmlspecialchars($payment['status']) ?></td>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                            <td><?= htmlspecialchars($payment['appointment_date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($_GET['section'] === 'reports'): ?>

        <h2>Reports</h2>
        <h3>Total Earnings Report</h3>
        <canvas id="earningsChart"></canvas>

        <h3>Total Bookings Report</h3>
        <canvas id="bookingsChart"></canvas>

        <script>
            const earningsCtx = document.getElementById('earningsChart').getContext('2d');
            const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
            
            const earningsData = {
                labels: [<?php while ($row = $earnings_result->fetch_assoc()) { echo '"' . $row['month'] . '", '; } ?>],
                datasets: [{
                    label: 'Total Earnings',
                    data: [<?php while ($row = $earnings_result->fetch_assoc()) { echo $row['total_earnings'] . ', '; } ?>],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }]
            };

            const bookingsData = {
                labels: [<?php while ($row = $bookings_result->fetch_assoc()) { echo '"' . $row['month'] . '", '; } ?>],
                datasets: [{
                    label: 'Total Bookings',
                    data: [<?php while ($row = $bookings_result->fetch_assoc()) { echo $row['total_bookings'] . ', '; } ?>],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderWidth: 1
                }]
            };

            new Chart(earningsCtx, {
                type: 'line',
                data: earningsData,
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            new Chart(bookingsCtx, {
                type: 'line',
                data: bookingsData,
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>

    <?php endif; ?>
</div>

</body>
</html>
