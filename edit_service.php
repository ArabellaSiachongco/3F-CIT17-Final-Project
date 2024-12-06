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
    font-family: 'Arial', sans-serif;
    background-color: #f9f3e5; /* Light cream background */
    color: #4b3c2d; /* Dark brown text */
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

/* Container */
.container {
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
    padding: 25px;
    background-color: #fff; /* White background for the form */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
}

/* Heading */
h1 {
    text-align: center;
    font-size: 2rem;
    color: #6f4f2f; /* Coffee brown */
    margin-bottom: 20px;
}

/* Error Message */
.error-message {
    color: #a94442; /* Muted red for errors */
    font-size: 1rem;
    margin-bottom: 15px;
    text-align: center;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Labels */
label {
    font-size: 1rem;
    font-weight: bold;
    color: #6f4f2f; /* Coffee brown */
}

/* Inputs and Textarea */
input[type="text"],
input[type="number"],
textarea {
    padding: 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-top: 5px;
    width: 100%;
    background-color: #fdf9f3; /* Light cream for subtle contrast */
    color: #4b3c2d; /* Dark brown for text */
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
    background-color: #6f4f2f; /* Coffee brown */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
    align-self: center;
}

button:hover {
    background-color: #4b3c2d; /* Darker brown on hover */
    transform: scale(1.02); /* Slight zoom for interactivity */
}

button:active {
    transform: scale(0.98); /* Click effect */
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    h1 {
        font-size: 1.5rem;
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
