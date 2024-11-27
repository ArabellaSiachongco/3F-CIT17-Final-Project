<?php

$servername = "localhost";    
$username = "root";   
$password = "";               
$dbname = "booking_system";  

$conn = new mysqli($servername, $username, $password, $dbname);

// Turn this to modal:
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully to the database!";
}
?>

