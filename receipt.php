<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the order ID from the query string
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($orderId > 0) {
    $order_query = "
        SELECT o.idorder, o.orderdate, o.totalamount, o.paytype, o.clock, c.customername, c.customerphone
        FROM `order` o
        JOIN customer c ON o.customerid = c.customerid
        WHERE o.idorder = ?
    ";

    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        $order_items_query = "SELECT itemnumber, itemprice, discount, quantity FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($order_items_query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Order not found.";
        exit;
    }
} else {
    echo "Invalid Order ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
</head>

<body>
    <h2>Order Receipt</h2>
    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['idorder']); ?></p>
    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customername']); ?></p>
    <p><strong>Customer Phone:</strong> <?php echo htmlspecialchars($order['customerphone']); ?></p>
    <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['orderdate']); ?></p>
    <p><strong>Order Time:</strong> <?php echo htmlspecialchars($order['clock']); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paytype']); ?></p>
    <p><strong>Total Amount:</strong> <?php echo number_format($order['totalamount'], 2); ?></p>
    <h3>Order Items</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Item Number</th>
                <th>Item Price</th>
                <th>Discount (%)</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['itemnumber']); ?></td>
                <td><?php echo number_format($item['itemprice'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['discount']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td><?php echo number_format($item['itemprice'] * $item['quantity'] * (1 - $item['discount'] / 100), 2); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="Orders.php">Back to Orders</a>
</body>

</html>