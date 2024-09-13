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

$error = ""; // Variable to store error message
$customer = []; // Initialize $customer as an empty array

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customernumber = $_POST['customernumber'];
    $customername = $_POST['customername'];
    $customerphone = $_POST['customerphone'];

    // Validate inputs
    if (empty($customername) || empty($customerphone)) {
        $error = "All fields are required.";
    } else {
        // Update query
        $sql = "UPDATE customer SET customername = ?, customerphone = ? WHERE customerid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $customername, $customerphone, $customernumber);

        if ($stmt->execute()) {
            // Redirect to customers.php after successful update
            header("Location: Customers.php");
            exit();
        } else {
            $error = "Error updating record: " . $conn->error;
        }

        $stmt->close();
    }
} else {
    // Fetch the customer data to pre-fill the form
    if (isset($_GET['id'])) {
        $customernumber = $_GET['id'];
        $sql = "SELECT customerid, customername, customerphone FROM customer WHERE customerid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $customernumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error = "Customer ID not provided.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Ensure style.css exists and is linked correctly -->
    <title>Edit Customer</title>
</head>
<style>
    h1 {
        margin: 30px 0 0 50px;
    }

    .form-container {
        text-align: center;
        background-color: black;
        width: 50%;
        margin: 3% auto;
        padding: 20px;
        border-radius: 25px;
    }

    input {
        border-color: transparent;
        border-radius: 25px;
        margin-top: 20px;
        width: 200px;
        height: 10px;
        padding: 10px;
    }

    .form-group {
        display: flex;
        align-items: center;

        margin-left: 22%;
    }

    .form-group label {
        width: 150px;
        color: #1d5488;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: larger;
        font-weight: 500;
        text-align: left;
    }

    .form-group input {
        border: 1px solid #1d5488;
        border-radius: 25px;
        width: 200px;
        height: 25px;
        padding: 10px;
        margin-left: 20px;
    }


    .error-message {
        margin-top: 10px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: red;
        text-align: center;
        margin-bottom: 5px;
    }
</style>

<body style="background-color: #092D65;">
    <h1 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:white;"><span style="color:red;">UPDATE</span>
        Customer Data</h1>
    <form method="post" action="edit_customers.php" class="form-container">
        <input type="hidden" name="customernumber" value="<?php echo htmlspecialchars(isset($customer['customerid']) ? $customer['customerid'] : ''); ?>">
        <div class="form-group">
            <label for="customername" id='edit-label'>Name</label>
            <input type="text" id="customername" name="customername" value="<?php echo htmlspecialchars(isset($customer['customername']) ? $customer['customername'] : ''); ?>">
        </div>
        <div class="form-group">
            <label for="customerphone" id='edit-label'>Phone</label>
            <input type="text" id="customerphone" name="customerphone" value="<?php echo htmlspecialchars(isset($customer['customerphone']) ? $customer['customerphone'] : ''); ?>">
        </div>
        <?php if (!empty($error)) : ?>
            <div class="error-message">
                <?php echo $error; ?></div>
        <?php endif; ?>

        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Cancel</button>

        <input type="submit" value="Update" style="background-color: #1d5488; color: white; height:45px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-color: transparent; border-radius: 25px; cursor: pointer; width:140px; margin-left:5%;">

    </form>
    <script>
        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Customers.php';
        });
    </script>
</body>

</html>