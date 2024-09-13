<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
} ?>
<?php
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

// Retrieve form data
$itemcode = $_POST['itemcode'];
$itemname = $_POST['itemname'];
$itemsize = $_POST['itemsize'];
$itemcolor = $_POST['itemcolor'];
$itemstock = $_POST['itemstock'];
$itemprice = $_POST['itemprice'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO inventory (itemcode,itemname, itemsize, itemcolor, itemstock, itemprice) VALUES (?,?, ?, ?, ?, ?)");
$stmt->bind_param("ssssii", $itemcode, $itemname, $itemsize, $itemcolor, $itemstock, $itemprice);

// Execute the statement
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();

// Redirect back to the inventory page
header("Location: Inventory.php");
exit();
?>
