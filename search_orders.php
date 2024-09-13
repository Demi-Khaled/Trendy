<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Check if the date parameter is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'];

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

    // Prepare SQL statement
    $sql = "SELECT idorder, customerid, items, totalamount, paytype, orderdate, clock FROM `order` WHERE orderdate = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    $orderData = array();

    // Fetch data
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orderData[] = $row;
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Return JSON response
    echo json_encode($orderData);
} else {
    // Handle case where 'date' parameter is not set
    echo json_encode(array('error' => 'Date parameter is missing'));
}
