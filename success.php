<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Order Success</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            margin-top: 50px;
        }

        .success-message {
            background-color: #1d5488;
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
        }

        .success-message h1 {
            font-size: 30px;
        }

        .success-message p {
            font-size: 20px;
        }

        .success-message a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>

<body style="background-color: #092D65;">
    <div class="success-message">
        <h1>Order Confirmed</h1>
        <p>Thank you! Your order has been placed successfully.</p>
        <a href="Orders.php">Go back to Orders</a>
    </div>
</body>

</html>