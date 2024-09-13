<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['itemnumber']) && isset($_GET['sizeColorMaterial'])) {
    $itemNumber = $_GET['itemnumber'];
    $sizeColorMaterial = $_GET['sizeColorMaterial'];

    list($itemSize, $itemColor, $itemMaterial) = explode('-', $sizeColorMaterial);

    $sql = "SELECT * FROM inventory WHERE itemnumber = ? AND itemsize = ? AND itemcolor = ? AND itemmaterial = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $itemNumber, $itemSize, $itemColor, $itemMaterial);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
}

$conn->close();
