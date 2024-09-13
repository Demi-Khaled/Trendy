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

if (isset($_GET['itemnumber'])) {
    $itemNumber = $_GET['itemnumber'];

    $sql = "SELECT DISTINCT itemsize, itemcolor, itemmaterial FROM inventory WHERE itemnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $itemNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
}

$conn->close();
