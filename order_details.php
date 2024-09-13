<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    $order_query = "SELECT o.*, c.customername, c.customerphone FROM `order` o JOIN customer c ON o.customerid = c.customerid WHERE o.order_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();

        $order_items_query = "SELECT * FROM `order_items` WHERE order_id = ?";
        $stmt = $conn->prepare($order_items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items_result = $stmt->get_result();

        $order_items = [];
        while ($row = $order_items_result->fetch_assoc()) {
            $order_items[] = $row;
        }
    } else {
        echo "Order not found.";
        exit;
    }
} else {
    echo "Invalid order ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    p {
        font-size: larger;
    }

    div {
        margin-left: 10%;
    }
</style>

<body style="background-color: #092D65; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; ">
    <h1 style="color:white;"><b style="color:red;">SHOW</b> Order Details</h1>
    <form action="" method="POST" style="background-color:black; border-radius:25px; color: white;">
        <br>
        <h2 style=" margin-left: 2.5%;">Customer Information</h2>
        <div>
            <p><b style="color:#1d5488;">Name:</b> <?php echo htmlspecialchars($order['customername']); ?></p>
            <p><b style="color:#1d5488;">Phone:</b> <?php echo htmlspecialchars($order['customerphone']); ?></p>

        </div>

        <h2 style=" margin-left: 2.5%;">Order Information</h2>
        <div>
            <p><b style="color:#1d5488;">Order ID:</b> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><b style="color:#1d5488;">Total Amount=</b> <?php echo htmlspecialchars($order['totalamount']); ?>
                <b>L.E</b>
            </p>
            <p><b style="color:#1d5488;">Payment Type:</b> <?php echo htmlspecialchars($order['paytype']); ?></p>
            <p><b style="color:#1d5488;">Order Date:</b> <?php echo htmlspecialchars($order['orderdate']); ?></p>
            <p><b style="color:#1d5488;">Status:</b> <?php echo htmlspecialchars($order['status']); ?></p>
        </div>
        <h2 style=" margin-left: 2.5%;">Order Table</h2>
        <table>
            <thead style="color:black;">
                <tr>
                    <th>Item Number</th>
                    <th>Quantity</th>
                    <th>Item Price</th>
                    <th>Discount</th>
                </tr>
            </thead>
            <tbody style="color: #1d5488;">
                <?php foreach ($order_items as $item) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['itemnumber']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['itemprice']); ?> <b>L.E</b></td>
                        <td><?php echo htmlspecialchars($item['discount']); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:45%;">Back</button>
        <script>
            document.getElementById('cancelBtn').addEventListener('click', function() {
                window.location.href = 'Home page.php';
            });
        </script>
        <br><br>
    </form>

</body>

</html>