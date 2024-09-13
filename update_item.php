<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
} ?>
<?php
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemnumber = $_POST['itemnumber'];
    $itemname = $_POST['itemname'];
    $itemmaterial = $_POST['itemmaterial'];
    $itemsize = $_POST['itemsize'];
    $itemcolor = $_POST['itemcolor'];
    $itemstock = $_POST['itemstock'];
    $itemprice = $_POST['itemprice'];
    $discount = $_POST['discount'];
    // SQL query to update item data

    $sql = "UPDATE item SET itemname = ?, itemmaterial = ?, itemsize = ?, itemcolor = ?, itemstock = ?, itemprice = ? ,discount=? WHERE itemnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdsii", $itemname, $itemmaterial, $itemsize, $itemcolor, $itemstock, $itemprice, $itemnumber, $discount);

    if ($stmt->execute()) {
        // Redirect back to the inventory page
        header("Location: Inventory.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>