<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Display message if set
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current date
$currentDate = date('Y-m-d');
$selectedDate = isset($_POST['searchDate']) ? $_POST['searchDate'] : $currentDate;

// Fetch order data
$sql = "SELECT order_id, customerid, totalamount, paytype, orderdate, clock, status FROM `order` WHERE orderdate = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

$orderData = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderData[] = $row;
    }
}
$stmt->close();
$conn->close();

// Determine the role for greeting
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Manager';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>


<body style="background-color: #092D65;">
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php" style="color:#1d5488;">Dashboard</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Managers.php">Managers</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav>
    </div>

    <h1 id="greet"><span style="color: red;"><b>HELLO</b></span>, <?php echo htmlspecialchars($role); ?></h1>

    <?php if ($message) : ?>
    <div class="message"
        style="text-align:center; color: red; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:medium;font-weight: bold;">
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="searchDate"
            style="color:white;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin-left:30px;font-size: large;"><Span
                style="color:red;"><b>SELECT</b></Span> Date</label>
        <input type="date" id="searchDate" name="searchDate"
            style="color:#1d5488; font-size:medium; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight:bold; border-radius: 25px; border-color:transparent; margin-left:10px;"
            value="<?php echo htmlspecialchars($selectedDate); ?>">
        <button type="submit"
            style="background-color: black;color: white;height: 35px;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size: large;padding: 0px 0px 3px 0px;border-color: transparent;margin-left: 5%;border-radius: 25px;width: 150px;cursor: pointer;">Search</button>
    </form>
    <input type="search" placeholder="Search" id="search" style="margin-left:30px;">

    <div class="main-container">
        <table style="width:100%;" class="orderTable" id="orderTable">
            <thead>
                <tr>
                    <th>Order detail</th>
                    <th>Customer ID</th>
                    <th>Total amount</th>
                    <th>Paytype</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody style="color:#1d5488; font-weight:500;">
                <?php
                if (!empty($orderData)) {
                    foreach ($orderData as $order) {
                        // Fetch order items for each order
                        $orderId = $order['order_id'];
                        $orderDetails = [];
                        $conn = new mysqli($servername, $username, $password, $dbname, $port);
                        $sqlItems = "SELECT itemnumber, itemprice, discount, quantity FROM order_items WHERE order_id = ?";
                        $stmtItems = $conn->prepare($sqlItems);
                        $stmtItems->bind_param("i", $orderId);
                        $stmtItems->execute();
                        $resultItems = $stmtItems->get_result();
                        if ($resultItems->num_rows > 0) {
                            while ($rowItems = $resultItems->fetch_assoc()) {
                                $orderDetails[] = $rowItems;
                            }
                        }
                        $stmtItems->close();
                        $conn->close();

                        // Calculate the difference in days between the order date and current date
                        $orderDateTime = new DateTime($order['orderdate'] . ' ' . $order['clock']);
                        $currentDateTime = new DateTime();
                        $interval = $orderDateTime->diff($currentDateTime);
                        $daysDifference = $interval->days;
                ?>
                <tr>
                    <td><a style="text-decoration: none;"
                            href="order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>">
                            <?php echo htmlspecialchars($order['order_id']); ?>
                        </a></td>
                    <td><?php echo htmlspecialchars($order['customerid']); ?></td>
                    <td><?php echo htmlspecialchars($order['totalamount']); ?> <b>L.E</b></td>
                    <td><?php echo htmlspecialchars($order['paytype']); ?></td>
                    <td><?php echo htmlspecialchars($order['orderdate']); ?></td>
                    <td><?php echo htmlspecialchars($order['clock']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <?php if ($daysDifference <= 14) : ?>
                        <form method="POST" action="returnOrder.php">
                            <input type="hidden" name="orderId"
                                value="<?php echo htmlspecialchars($order['order_id']); ?>">
                            <input type="hidden" name="orderDetails"
                                value='<?php echo htmlspecialchars(json_encode($orderDetails)); ?>'>
                            <button type="submit"
                                style="background-color: red;height:20px;  color: white; border: none; border-radius: 25px; cursor: pointer;width:60px">Return</button>
                        </form>
                        <?php else : ?>
                        <button type="button"
                            style="background-color: grey; color: white; border: none; border-radius: 5px; cursor: not-allowed;"
                            disabled>Return</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="8" style="text-align: center; font-weight: bolder; color: red; font-size: larger;">No orders found for the selected date.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
    document.getElementById('search').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var table = document.getElementById('orderTable');
        var rows = table.getElementsByTagName('tr');

        for (var i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
            var orderIdCell = rows[i].getElementsByTagName('td')[0]; // Order ID is the first cell
            if (orderIdCell.innerText.toLowerCase().indexOf(searchValue) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });

    function openReturnTab(orderId) {
        window.location.href = 'returnOrder.php?orderId=' + orderId;
    }
    </script>

    <script src="script.js"></script>
</body>

</hbody