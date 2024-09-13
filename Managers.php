<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require 'access_control.php';
restrict_access(['Management']); // Only allow managers to access this page

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// SQL query to retrieve data
$sql = "SELECT idadmin, name, phone, address, email FROM admin";
$result = $conn->query($sql);

// Check for SQL query errors
if ($result === false) {
    die('Error in SQL query: ' . $conn->error);
}

// Initialize an empty array to store the rows of data
$rows = array();

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Fetch rows of data from the result set
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Managers</title>
    <link rel="stylesheet" href="style.css">

</head>
<style>
    #search {
        margin-bottom: 10px;
        padding: 5px;
        width: 200px;
        height: 35px;
        border-radius: 25px;
    }

    #add-btn {
        background-color: black;
        color: white;
        height: 35px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: large;
        padding: 0px 0px 3px 0px;
        border-color: transparent;
        margin-left: 70%;
        border-radius: 25px;
        width: 150px;
        cursor: pointer;
    }
</style>

<body style="background-color: #092D65;">
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php">Home</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Managers.php" style="color:#1d5488;">Managers</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>

            </ul>
        </nav>
    </div>
    <input type="search" placeholder="Search" id="search">
    <button id="add-btn" class="animated-button" style="position:absolute;top:17.5%;">Add manager</button>
    <section id="manager">
        <table id="manager-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)) : ?>
                    <tr>
                        <td colspan="4" class="no-data" style="text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: bolder; color: red; font-size: larger;">
                            No managers found.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr style="color:#1d5488; font-weight:500;">
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <script>
        document.getElementById('add-btn').addEventListener('click', function() {
            // Redirect to the add employee page
            window.location.href = 'add_manager.php';
        });
        document.getElementById('search').addEventListener('keyup', function() {
            var searchValue = this.value.toLowerCase();
            var table = document.getElementById('manager-table');
            var rows = table.getElementsByTagName('tr');

            for (var i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
                var cells = rows[i].getElementsByTagName('td');
                var match = false;

                for (var j = 0; j < cells.length; j++) {
                    if (cells[j].innerText.toLowerCase().indexOf(searchValue) > -1) {
                        match = true;
                        break;
                    }
                }

                if (match) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>

</html>