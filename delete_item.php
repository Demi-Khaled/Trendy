<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'access_control.php';
restrict_access(['Management']); // Only allow managers to access this page

// Database connection parameters
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "trendy"; // Your database name
$port = 3307;
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$iditem = $_POST['itemnumber'];

// Delete item
$sql = "DELETE FROM inventory WHERE itemnumber='$iditem'";

if ($conn->query($sql) === TRUE) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();

// Redirect back to the inventory page
header("Location: Inventory.php");
exit();
