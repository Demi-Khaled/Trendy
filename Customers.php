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

// Initialize an empty array for rows
$rows = array();

// SQL query to retrieve data
$sql = "SELECT customerid, customername, customerphone FROM customer";
$result = $conn->query($sql);

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Fetch rows of data from the result set
    while ($row = $result->fetch_assoc()) {
        // Format the date
        $rows[] = $row;
    }
}
// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
table thead tr:first-child {

    text-align: center;
}

#search {
    margin-bottom: 10px;
    padding: 5px;
    width: 200px;
    height: 35px;
    border-radius: 25px;
}
</style>

<body style="background-color: #092D65;">
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php">Dashboard</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php" style="color:#1d5488;">Customers</a></li>
                <li><a href="Managers.php">Managers</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav>
    </div>

    <input type="search" placeholder="Search" id="search">
    <section id="employee" class="customer">

        <table id="customer-table">
            <thead>
                <tr>
                    <th style="color: black;text-align: center;">Customer id</th>
                    <th style="color: black;text-align: center;">Name</th>
                    <th style="color: black;text-align: center;">Phone</th>
                    <th style="color: black;text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody style="color:#1d5488; font-weight:500;">
                <?php if (!empty($rows)) : ?>
                <?php foreach ($rows as $row) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['customerid']); ?></td>
                    <td><?php echo htmlspecialchars($row['customername']); ?></td>
                    <td><?php echo htmlspecialchars($row['customerphone']); ?></td>
                    <td>
                        <button class="edit-btn" data-id="<?php echo htmlspecialchars($row['customerid']); ?>"
                            style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:50px; border-radius:25px; border-color:transparent;cursor:pointer;">Edit</button>
                        <button class="delete-btn" data-id="<?php echo htmlspecialchars($row['customerid']); ?>"
                            style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:60px; border-radius:25px; border-color:transparent;cursor:pointer;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else : ?>
                <tr>
                    <td colspan="6" style=" text-align: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-weight: bolder;
                color: red;
                font-size: larger;">There are no customers</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <script>
    document.getElementById('search').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var table = document.getElementById('customer-table');
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

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            // Redirect to the edit page with the customer ID
            window.location.href = 'edit_customers.php?id=' + id;
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            // Redirect to the delete script with the customer ID
            window.location.href = 'delete_customers.php?id=' + id;
        });
    });
    </script>
</body>

</html>