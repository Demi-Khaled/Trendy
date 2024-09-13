<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = '';
$orderDetails = [];

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

// Check if the request method is POST and handle accordingly
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirmReturn'])) {
        $orderId = $_POST['orderId'];
        $orderDetails = json_decode($_POST['orderDetails'], true);
        $returnQuantities = $_POST['returnQuantity'] ?? [];
        $refundAmount = 0;


        // Calculate the total refund amount
        foreach ($orderDetails as $item) {
            $itemNumber = $item['itemnumber'];
            $quantity = intval($returnQuantities[$itemNumber] ?? 0);
            $itemPrice = floatval($item['itemprice']);
            $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
            $totalItemPrice = $quantity * $itemPrice * (1 - $discount / 100);

            $refundAmount += $totalItemPrice;
        }

        // Check if refund amount is 0
        if ($refundAmount == 0) {
            $_SESSION['message'] = "Cannot process return for order $orderId with a total refund amount of 0.";
            header('Location: returnOrder.php?orderId=' . urlencode($orderId) . '&orderDetails=' . urlencode(json_encode($orderDetails)));
            exit;
        }

        $conn->begin_transaction(); // Start transaction

        try {
            // Update inventory quantities and handle return items
            foreach ($orderDetails as $item) {
                $itemNumber = $item['itemnumber'];
                $originalQuantity = intval($item['quantity']);
                $returnQuantity = intval($returnQuantities[$itemNumber] ?? 0);

                if ($returnQuantity > 0) {
                    $sql = "UPDATE inventory SET itemstock = itemstock + ? WHERE itemnumber = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $returnQuantity, $itemNumber);
                    $stmt->execute();
                    $stmt->close();

                    if ($returnQuantity == $originalQuantity) {
                        // Delete items with all quantities returned from order_items
                        $sql = "DELETE FROM order_items WHERE order_id = ? AND itemnumber = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $orderId, $itemNumber);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        // Update quantity for items with partial returns
                        $newQuantity = $originalQuantity - $returnQuantity;
                        $sql = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND itemnumber = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iii", $newQuantity, $orderId, $itemNumber);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }

            // Calculate new total amount
            $sql = "SELECT SUM(itemprice * quantity * (1 - discount / 100)) AS totalAmount FROM order_items WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $orderTotalRow = $result->fetch_assoc();
            $originalTotalAmount = $orderTotalRow['totalAmount'];

            // Subtract refund amount from total amount
            $newTotalAmount = $originalTotalAmount;

            // Update order status and total amount
            $sql = "UPDATE `order` SET status = 'Returned', totalamount = ? WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $newTotalAmount, $orderId);
            $stmt->execute();
            $stmt->close();

            $conn->commit(); // Commit transaction
            $_SESSION['message'] = "Order $orderId has been returned successfully. Refund Amount: " . number_format($refundAmount, 2) . " L.E";
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $_SESSION['message'] = "Failed to return order $orderId: " . $e->getMessage();
        } finally {
            $conn->close();
        }

        header('Location: returnOrder.php?orderId=' . urlencode($orderId) . '&orderDetails=' . urlencode(json_encode($orderDetails)));
        exit;
    } else {
        $orderId = $_POST['orderId'];
        $orderDetails = json_decode($_POST['orderDetails'], true);
    }
} elseif (isset($_GET['orderId'])) {
    $orderId = $_GET['orderId'];

    // Fetch order details from the database
    $sql = "SELECT itemnumber, itemprice, discount, quantity FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $orderId);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $orderDetails = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No items found for order ID: $orderId</p>";
    }
    $stmt->close();
}

$items = $orderDetails ? $orderDetails : [];

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Order</title>
    <link rel="stylesheet" href="style.css">
    <style>
        h1 {
            margin: 30px 0 0 50px;
        }

        body {
            background-color: #1d5488;
            color: aliceblue;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        table {
            margin-left: 5%;
            width: 90%;
            border-collapse: collapse;
        }
    </style>
</head>

<body style="background-color: #092D65;">
    <h1><span style="color:red;">RETURN</span> Order</h1>
    <form method="POST" action="" style="background-color:black; border-radius:25px;">
        <input type="hidden" name="orderId" value="<?php echo htmlspecialchars($orderId); ?>">
        <input type="hidden" name="orderDetails" value='<?php echo htmlspecialchars(json_encode($orderDetails)); ?>'>
        <h3 style="margin-left: 80px;">Are you sure you want to return order
            <?php echo htmlspecialchars($orderId); ?>?</h3>

        <!-- Display Order Details inre Table -->
        <table class="orderTable">
            <thead style="color:Black;">
                <tr>
                    <th>Item ID</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Return Quantity</th>
                </tr>
            </thead>
            <tbody style="color:#1d5488;">
                <?php if (!empty($items)) : ?>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['itemnumber']); ?></td>
                            <td>
                                <?php
                                $quantity = intval($item['quantity']);
                                $totalItemPrice = floatval($item['itemprice']) * $quantity;
                                $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                                $totalItemPrice -= $totalItemPrice * ($discount / 100);
                                $pricePerItem = $totalItemPrice / $quantity;
                                echo htmlspecialchars($pricePerItem) . ' <b>L.E</b>';
                                ?>
                            </td>
                            <td class="originalQuantity"><?php echo htmlspecialchars($quantity); ?></td>
                            <td class="totalPrice"><?php echo htmlspecialchars($totalItemPrice) . ' <b>L.E</b>'; ?></td>
                            <td><input type="number" name="returnQuantity[<?php echo htmlspecialchars($item['itemnumber']); ?>]" min="0" max="<?php echo htmlspecialchars($quantity); ?>" value="0" onchange="updateQuantitiesAndRefund(this)" style="border-radius:25px; border-top-color:#1d5488; border-color:transparent; background-color:#1d5488;color:aliceblue;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" style="text-align: center; font-weight: bolder; color: red; font-size: larger;">No
                            items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot style="color:Black;">
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Total Order Amount:</td>
                    <td colspan="4" id="totalOrderAmount" style="font-weight: bold;"></td>
                </tr>
            </tfoot>
        </table>
        <br>

        <label style="font-size:larger; margin-left:100px;"><b style="color:red;">Total Refund Amount= </b><span id="refundAmount" style="font-weight:500;">0</span> <b>L.E</b></label><br>

        <?php if (isset($_SESSION['message'])) : ?>
            <div class="message" style="text-align:center; color: red; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:medium;font-weight: bold;">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                <?php unset($_SESSION['message']); ?>
            </div>

        <?php endif; ?>
        <br>
        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:35%;">Cancel</button>
        <button id="confirmReturn" type="submit" name="confirmReturn" style="background-color: #e6e6e6; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Confirm</button>
        <br><br>
    </form>
    <script>
        function updateQuantitiesAndRefund(inputElement) {
            const row = inputElement.closest('tr');
            const priceCell = row.querySelector('td:nth-child(2)');
            const originalQuantityCell = row.querySelector('.originalQuantity');
            const totalPriceCell = row.querySelector('.totalPrice');
            const pricePerItem = parseFloat(priceCell.textContent.replace(' L.E', ''));
            const originalQuantity = parseInt(originalQuantityCell.dataset.originalQuantity);
            const returnQuantity = parseInt(inputElement.value);

            // Validate return quantity to be within valid range
            if (isNaN(returnQuantity) || returnQuantity < 0 || returnQuantity > originalQuantity) {
                inputElement.value = 0;
                return;
            }

            // Calculate the new total price for the row
            const newTotalPrice = (originalQuantity - returnQuantity) * pricePerItem;
            totalPriceCell.textContent = newTotalPrice.toFixed(2) + ' L.E';

            // Update the remaining quantity in the originalQuantityCell
            originalQuantityCell.textContent = originalQuantity - returnQuantity;

            // Update the total refund amount and total order amount
            calculateRefund();
        }

        function calculateRefund() {
            const rows = document.querySelectorAll('.orderTable tbody tr');
            let totalRefund = 0;
            let totalOrderAmount = 0;

            rows.forEach(row => {
                const priceCell = row.querySelector('td:nth-child(2)');
                const totalPriceCell = row.querySelector('.totalPrice');
                const returnQuantityInput = row.querySelector('input[name^="returnQuantity"]');
                const pricePerItem = parseFloat(priceCell.textContent.replace(' L.E', ''));
                const totalPrice = parseFloat(totalPriceCell.textContent.replace(' L.E', ''));
                const returnQuantity = parseInt(returnQuantityInput.value);

                // Ensure valid return quantity
                if (!isNaN(returnQuantity) && returnQuantity >= 0) {
                    totalRefund += pricePerItem * returnQuantity;
                    totalOrderAmount += totalPrice;
                }
            });

            document.getElementById('refundAmount').textContent = totalRefund.toFixed(2);
            document.getElementById('totalOrderAmount').textContent = totalOrderAmount.toFixed(2) + ' L.E';
        }

        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Home page.php';
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.originalQuantity').forEach(cell => {
                cell.dataset.originalQuantity = cell.textContent;
            });
            calculateRefund();
        });

        // Attach the update function to all input elements
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name^="returnQuantity"]').forEach(input => {
                input.addEventListener('input', function() {
                    updateQuantitiesAndRefund(this);
                });
            });
        });
    </script>


</body>

</html>