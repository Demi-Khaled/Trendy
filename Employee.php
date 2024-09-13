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

// SQL query to retrieve data
$sql = "SELECT idemp, empname, job, empemail, empphone, empaddress FROM employee";
$result = $conn->query($sql);

// Initialize an empty array to store the rows of data
$rows = array();

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Fetch rows of data from the result set
    while ($row = $result->fetch_assoc()) {
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
    <title>Employee Data</title>
    <link rel="stylesheet" href="style.css"> <!-- Ensure style.css exists and is linked correctly -->
    <style>
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

        .animated-button {
            color: white;
            /* Set initial color */
            text-decoration: none;
            /* Apply transition to color */
        }

        .animated-button:hover {
            color: #1d5488;
            transition: 0.3s;
            /* Change color on hover */
        }

        #employee-table {
            width: 100%;
            border-collapse: collapse;
        }

        #employee-table th {
            color: black;
            text-align: center;
        }

        #employee-table td {
            text-align: center;
        }

        #search {
            margin-bottom: 10px;
            padding: 5px;
            width: 200px;
            height: 35px;
            border-radius: 25px;
        }

        .no-data {
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: bolder;
            color: red;
            font-size: larger;
        }
    </style>
</head>

<body style="background-color: #092D65;">
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php">Dashboard</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php" style="color:#1d5488;">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Managers.php">Managers</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav><input type="search" placeholder="Search" id="search">
        <button id="add-btn" class="animated-button" style="position:absolute;top:17.5%;">Add Employee</button>
    </div>



    <section id="employee" class="employee">
        <table id="employee-table">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Job</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody style="color:#1d5488; font-weight:500;">
                <?php if (empty($rows)) : ?>
                    <tr>
                        <td colspan="7" class="no-data" style="text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: bolder;
            color: red;
            font-size: larger;">There are no employees</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['idemp']); ?></td>
                            <td><?php echo htmlspecialchars($row['empname']); ?></td>
                            <td><?php echo htmlspecialchars($row['job']); ?></td>
                            <td><?php echo htmlspecialchars($row['empemail']); ?></td>
                            <td><?php echo htmlspecialchars($row['empphone']); ?></td>
                            <td><?php echo htmlspecialchars($row['empaddress']); ?></td>
                            <td>
                                <button class="edit-btn" data-id="<?php echo htmlspecialchars($row['idemp']); ?>" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:50px; border-radius:25px; border-color:transparent;cursor:pointer;">Edit</button>
                                <button class="delete-btn" data-id="<?php echo htmlspecialchars($row['idemp']); ?>" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:60px; border-radius:25px; border-color:transparent;cursor:pointer;">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- JavaScript for searching and handling actions -->
    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            var searchValue = this.value.toLowerCase();
            var table = document.getElementById('employee-table');
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

        // Handle edit and delete button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                // Redirect to the edit page with the employee ID
                window.location.href = 'edit_employee.php?id=' + id;
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this record?')) {
                    var id = this.getAttribute('data-id');
                    // Redirect to the delete script with the employee ID
                    window.location.href = 'delete_employee.php?id=' + id;
                }
            });
        });

        // Handle add employee button click
        document.getElementById('add-btn').addEventListener('click', function() {
            // Redirect to the add employee page
            window.location.href = 'add_employee.php';
        });
    </script>
</body>

</html>