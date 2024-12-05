<?php
session_start();
require_once 'db.php'; // Include database connection

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get the service_id from the URL
if (isset($_GET['service_id'])) {
    $service_id = intval($_GET['service_id']);

    // Fetch the service details from the database
    $sql = "SELECT * FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Service not found
        header('Location: admin_dashboard.php?section=services');
        exit();
    }

    $service = $result->fetch_assoc();
} else {
    // No service ID provided
    header('Location: admin_dashboard.php?section=services');
    exit();
}

// Handle the form submission to update the service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = $_POST['service_name'];
    $description = $_POST['description']; // Updated to 'description'
    $price = $_POST['price']; // Updated to 'price' instead of 'service_price'
    $service_duration = $_POST['service_duration'];

    if (empty($service_name) || empty($description) || empty($price) || empty($service_duration)) {
        $error_message = 'All fields are required.';
    } else {
        // Update the service in the database
        $update_query = "UPDATE services SET service_name = ?, description = ?, price = ?, duration = ? WHERE service_id = ?"; // Updated 'service_price' to 'price' and 'service_duration' to 'duration'
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('ssdis', $service_name, $description, $price, $service_duration, $service_id); // Updated 'service_price' to 'price' and 'service_duration' to 'duration'

        if ($update_stmt->execute()) {
            // Redirect back to the admin dashboard after successful update
            header('Location: admin_dashboard.php?section=services');
            exit();
        } else {
            $error_message = 'Failed to update service.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service</title>
</head>
<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Container */
.container {
    width: 80%;
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Heading */
h1 {
    text-align: center;
    font-size: 2em;
    margin-bottom: 20px;
}

/* Error Message */
div[style="color: red;"] {
    color: red;
    font-size: 1.2em;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Labels */
label {
    font-size: 1.1em;
    font-weight: bold;
}

/* Inputs and Textarea */
input[type="text"],
input[type="number"],
textarea {
    padding: 10px;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 5px;
    width: 100%;
}

input[type="number"] {
    width: auto;
}

/* Textarea */
textarea {
    height: 150px;
    resize: vertical;
}

/* Button */
button {
    padding: 12px 20px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.1em;
    transition: background-color 0.3s ease;
    width: 200px;
    align-self: center;
}

button:hover {
    background-color: #2980b9;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
        margin: 10px;
    }

    button {
        width: 100%;
    }
}

</style>
<body>

<h1>Edit Service</h1>

<?php if (isset($error_message)): ?>
    <div style="color: red;"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Edit Service Form -->
<form method="POST" action="edit_service.php?service_id=<?php echo $service_id; ?>">
    <label for="service_name">Service Name:</label><br>
    <input type="text" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required><br>

    <label for="description">Service Description:</label><br>
    <textarea name="description" required><?php echo htmlspecialchars($service['description']); ?></textarea><br>

    <label for="price">Price:</label><br> <!-- Updated 'service_price' to 'price' -->
    <input type="number" name="price" value="<?php echo htmlspecialchars($service['price']); ?>" step="0.01" required><br>

    <label for="service_duration">Duration (minutes):</label><br> <!-- Updated 'service_duration' to 'duration' -->
    <input type="number" name="service_duration" value="<?php echo htmlspecialchars($service['duration']); ?>" required><br>

    <button type="submit">Update Service</button>
</form>

</body>
</html>
