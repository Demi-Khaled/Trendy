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

function checkCustomer($conn, $name, $phone)
{
    $check_query = "SELECT customerid FROM customer WHERE customername=? AND customerphone=?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($customerId);
        $stmt->fetch();
        return array("exists" => true, "customerid" => $customerId);
    } else {
        $insert_query = "INSERT INTO customer (customername, customerphone) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $name, $phone);
        if ($stmt->execute()) {
            $customerId = $stmt->insert_id;
            return array("exists" => false, "customerid" => $customerId);
        } else {
            return array("error" => $stmt->error);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderData'])) {
    $orderData = json_decode($_POST['orderData'], true);
    if (!$orderData) {
        header('Location: Orders.php');
        exit;
    }
}

$error = "";
$customerId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customerName'], $_POST['customerPhoneNumber'])) {
    $name = $conn->real_escape_string($_POST['customerName']);
    $phone = $conn->real_escape_string($_POST['customerPhoneNumber']);

    if (empty($name) || empty($phone)) {
        $error = "Please fill in both customer name and phone number.";
    } else {
        $customerData = checkCustomer($conn, $name, $phone);
        if (isset($customerData['error'])) {
            $error = $customerData['error'];
        } else {
            if (isset($customerData['customerid'])) {
                $customerId = $customerData['customerid'];
            } else {
                $error = "Customer ID not found.";
            }
        }
    }
}

if ($error) {
    echo $error;
    exit;
}

if ($customerId) {
    $totalPrice = 0;
    foreach ($orderData as $item) {
        if (isset($item['itemprice'], $item['quantity'])) {
            $itemPrice = floatval($item['itemprice']);
            $discount = floatval($item['discount']);
            $quantity = intval($item['quantity']);
            $totalPrice +=  $quantity * $itemPrice - ($itemPrice * ($quantity * ($discount / 100)));
        }
    }

    $orderDate = date('Y-m-d H:i:s');
    $time = date('H:i:s');

    $conn->begin_transaction();

    try {
        $insert_order_query = "INSERT INTO `order` (customerid, totalamount, paytype, orderdate, clock, status) VALUES (?, ?, ?, ?, ?, ?)";
        $status = 'Pending'; // Default status
        $paymentMethod = isset($_POST['paymentMethod']) ? $conn->real_escape_string($_POST['paymentMethod']) : 'Cash';

        $stmt = $conn->prepare($insert_order_query);
        $stmt->bind_param("idssss", $customerId, $totalPrice, $paymentMethod, $orderDate, $time, $status);
        if ($stmt->execute() === FALSE) {
            throw new Exception("Error inserting order: " . $stmt->error);
        }

        $orderId = $stmt->insert_id; // Get the new order ID

        foreach ($orderData as $item) {
            if (isset($item['itemnumber'], $item['itemprice'], $item['quantity'], $item['discount'])) {
                $itemPrice = floatval($item['itemprice']);
                $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                $quantity = intval($item['quantity']);
                $itemNumber = $item['itemnumber'];

                $insert_order_item_query = "INSERT INTO `order_items` (order_id, itemnumber, discount, quantity, itemprice) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_order_item_query);
                $stmt->bind_param("iidid", $orderId, $itemNumber, $discount, $quantity, $itemPrice);
                if ($stmt->execute() === FALSE) {
                    throw new Exception("Error inserting order item: " . $stmt->error);
                }

                $update_inventory_query = "UPDATE inventory SET itemstock = itemstock - ? WHERE itemnumber = ?";
                $stmt = $conn->prepare($update_inventory_query);
                $stmt->bind_param("ii", $quantity, $itemNumber);
                if ($stmt->execute() === FALSE) {
                    throw new Exception("Error updating inventory: " . $stmt->error);
                }
            } else {
                throw new Exception("Missing item details in order data.");
            }
        }

        $update_order_query = "UPDATE `order` SET totalamount=? WHERE order_id=?";
        $stmt = $conn->prepare($update_order_query);
        $stmt->bind_param("di", $totalPrice, $orderId);
        if ($stmt->execute() === FALSE) {
            throw new Exception("Error updating order: " . $stmt->error);
        }

        $conn->commit();
        header('Location: success.php'); // Redirect to success.php upon successful order
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Transaction failed: " . $e->getMessage();
        echo $error;
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Order Confirmation</title>
    <style>
        h1 {
            font-size: 30px;
            font-weight: 500;
            padding-left: 25px;
            color: rgb(255, 255, 255);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #paymentForm {
            background-color: black;
            border-radius: 25px;
        }

        form .customer-info {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            align-items: center;
        }

        .confirmation-container {
            margin: 20px;
        }

        .confirmation-actions {
            margin-top: 20px;
        }

        .money-input-container {
            margin-top: 20px;
            display: none;
        }

        .money-input-container input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .error-message {
            color: red;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
    <script>
        function handlePaymentChange() {
            const paymentMethod = document.getElementById('payment').value;
            const moneyInputContainer = document.querySelector('.money-input-container');
            if (paymentMethod === 'Cash') {
                moneyInputContainer.style.display = 'block';
            } else {
                moneyInputContainer.style.display = 'none';
            }
        }

        function calculateReminder() {
            const totalPriceText = document.getElementById('total-price').innerText;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d.-]/g, ''));
            const moneyReceived = parseFloat(document.getElementById('money-received').value);
            if (!isNaN(moneyReceived)) {
                const reminder = moneyReceived - totalPrice;
                const reminderFormatted = formatCurrency(reminder);
                document.getElementById('reminder-message').innerText = reminderFormatted;
            } else {
                document.getElementById('reminder-message').innerText = formatCurrency(0);
            }
        }

        function formatCurrency(amount) {
            return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' L.E';
        }

        function checkCustomer() {
            const phoneNumber = document.getElementById('phone-number').value.trim();
            const name = document.getElementById('customerName').value.trim();

            if (name === '' || phoneNumber === '') {
                alert('Please fill in both the customer name and phone number.');
                return;
            }

            fetch('confirm_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        customerName: name,
                        customerPhoneNumber: phoneNumber
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error); // Display specific error message
                        return;
                    }
                    if (data.exists) {
                        alert('Customer already exists!');
                    } else {
                        alert('New customer added!'); // Alert for new customer insertion
                    }
                    document.getElementById('customerId').value = data.customerId;

                    // **Testing purposes only:**
                    // To verify the function works for checking customer existence,
                    // uncomment the following line and inspect the console for the response:
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function checkFormFilled() {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = ''; // Clear previous messages
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            let allFilled = true;
            let messages = [];
            for (const field of requiredFields) {
                if (!field.value.trim()) {
                    allFilled = false;
                    if (field.id === 'customerName' || field.id === 'customerPhoneNumber') {
                        messages.push('Please fill in the name and phone number of the customer.');
                    }
                }
            }
            if (messages.length > 0) {
                messageContainer.innerHTML = messages.join('<br>');
            }
            return allFilled;
        }

        function validateForm(event) {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = ''; // Clear previous messages
            let messages = [];

            // Check if customer information fields are filled
            const customerFilled = checkFormFilled();
            if (!customerFilled) {
                messages.push('Please fill in the name and phone number of the customer.');
            }

            // Check if payment method is selected
            const paymentMethod = document.getElementById('payment').value;
            if (paymentMethod === 'Select') {
                messages.push('Please select a payment method.');
            }

            // Check if cash payment and money received is sufficient
            const totalPriceText = document.getElementById('total-price').innerText;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d.-]/g, ''));
            const moneyReceived = parseFloat(document.getElementById('money-received').value);

            if (paymentMethod === 'Cash') {
                if (isNaN(moneyReceived) || moneyReceived < totalPrice) {
                    messages.push('The money received is less than the total price or not provided.');
                }
            }

            // Display messages if there are any
            if (messages.length > 0) {
                messageContainer.innerHTML = messages.join('<br>');
                event.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</head>

<body style="background-color: #092D65;">
    <h1><span style="color:red;">CONFIRM</span> New Order</h1>

    <form id="paymentForm" method="POST" action="confirm_order.php" onsubmit="return validateForm(event)">

        <input type="hidden" name="orderData" value='<?php echo json_encode($orderData); ?>'>

        <div class="customer-info" style="background-color:black;width:75%; border-radius: 25px;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;margin-left:12.5%;">

            <div style="text-align: center; margin-bottom: 20px; ">
                <h2 style=" color: #1d5488;">Customer Information</h2>
            </div>


            <div style="margin-bottom: 10px;">
                <label for="customerName" style=" width: 150px; color: #1d5488; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size: larger;font-weight: 500; margin-right: 97px;">Name</label>
                <input type="text" id="customerName" name="customerName" style="width: 45%; padding: 10px; border: 1px solid #1d5488; border-radius: 25px; padding-left: 20px; ">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="phone-number" style=" width: 150px; color: #1d5488; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: larger; font-weight: 500; margin-right: 20px;">Phone
                    number</label>
                <input type="text" name="customerPhoneNumber" id="phone-number" style=" width:45%; padding: 10px; border: 1px solid #1d5488; border-radius: 25px;  padding-left: 20px;">
            </div>
        </div>


        <table>
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Material</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
            </thead>

            <tbody style="color:#1d5488;">
                <?php foreach ($orderData as $item) : ?>
                    <tr>
                        <td><?= isset($item['itemnumber']) ? htmlspecialchars($item['itemnumber']) : '' ?></td>
                        <td><?= isset($item['itemname']) ? htmlspecialchars($item['itemname']) : '' ?></td>
                        <td><?= isset($item['itemsize']) ? htmlspecialchars($item['itemsize']) : '' ?></td>
                        <td><?= isset($item['itemcolor']) ? htmlspecialchars($item['itemcolor']) : '' ?></td>
                        <td><?= isset($item['itemmaterial']) ? htmlspecialchars($item['itemmaterial']) : '' ?></td>
                        <td><?= isset($item['itemprice']) ? htmlspecialchars($item['itemprice'])  : '' ?></td>
                        <td><?= isset($item['discount']) ? htmlspecialchars($item['discount']) : '' ?></td>
                        <td><?= isset($item['quantity']) ? htmlspecialchars($item['quantity']) : '' ?></td>
                        <td>
                            <?php
                            if (isset($item['itemprice'], $item['quantity'])) {
                                $itemPrice = floatval($item['itemprice']);
                                $discount = floatval($item['discount']);
                                $quantity = intval($item['quantity']);
                                if ($discount) {
                                    $totalItemPrice = $quantity * $itemPrice - ($itemPrice * ($quantity * ($discount / 100)));
                                } else {
                                    $totalItemPrice = $itemPrice * $quantity;
                                }
                                echo htmlspecialchars(number_format($totalItemPrice, 2)) . ' L.E';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="8" style="text-align: right;"><b>Total Price</b></td>
                    <td id="total-price"><b>
                            <?php
                            $totalPrice = 0;
                            foreach ($orderData as $item) {
                                if (isset($item['itemprice'], $item['quantity'])) {
                                    $itemPrice = floatval($item['itemprice']);
                                    $discount = floatval($item['discount']);
                                    $quantity = intval($item['quantity']);
                                    $totalPrice +=  $quantity * $itemPrice - ($itemPrice * ($quantity * ($discount / 100)));
                                }
                            }
                            echo htmlspecialchars(number_format($totalPrice, 2)) . ' L.E';
                            ?></b>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div id="billing">
            <select name="Payment" id="payment" onchange="handlePaymentChange()" style="background-color: white; color:#1d5488; height: 50px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-radius: 25px; margin-top: 55px;padding-left:10px;margin-left:2%;">
                <option value="Select" selected disabled required>Select Payment Method</option>
                <option value="Cash" required>Cash</option>
                <option value="Credit Card" required>Credit Card</option>
            </select>
        </div>

        <div class="money-input-container">
            <input type="number" id="money-received" name="moneyReceived" step="1" oninput="calculateReminder()" style="width:13.5%;border-radius: 25px;margin-left:2%;">
        </div>

        <?php
        if ($error) {
            echo "<div class='error'>$error</div>";
        }
        ?>
        <div id="message-container" class="message" style="color:red; text-align:center;font-weight:bold; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        </div>
        <br>

        <div style="align-items:center;display: flex;flex-direction: row; padding:0% 40% 0% 40%;">
            <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer;margin-right:5%;">Cancel</button>


            <button type="submit" style="background-color:#1d5488; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: larger; border-color: transparent; border-radius: 25px;height:45px; width: 140px;cursor:pointer; ">Confirm</button>
        </div>
        <br><br>

    </form>
    <script>
        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Orders.php';
        });
    </script>
    </div>
</body>

</html>