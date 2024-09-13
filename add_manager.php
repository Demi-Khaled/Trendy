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

// Initialize variables for error handling
$success_message = '';
$error = '';

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';


    // Set the username to 'admin'
    $username = 'admin';

    // Validate inputs
    if (empty($name) || empty($phone) || empty($email) || empty($address) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO admin (username,name, password, phone, address, email) VALUES (?,?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $name, $hashed_password, $phone, $address, $email);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: Managers.php");
        } else {
            $error = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
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
    <title>Add Manager</title>
    <link rel="stylesheet" href="style.css">
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
            height: 30px;
            /* Fixed height */
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
            height: 30px;
            /* Fixed height */
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
</head>

<body style="background-color: #092D65;">
    <section id="add-manager">
        <h1 style="color: aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <span style="color:red;">ADD</span> New Manager
        </h1>
        <form method="POST" action="add_manager.php" class="form-container">
            <div class="form-group">
                <label for="useremp" id="edit-label">Username</label>
                <input type="text" id="useremp" name="username" value="admin" readonly>
            </div>

            <div class="form-group">
                <label for="emppassword" id="edit-label">Password</label>
                <input type="password" id="emppassword" name="password">
            </div>

            <div class="form-group">
                <label for="empfname" id="edit-label">Name</label>
                <input type="text" id="name" name="name">
            </div>

            <div class="form-group">
                <label for="empemail" id="edit-label">Email</label>
                <input type="email" id="empemail" name="email">
            </div>

            <div class="form-group">
                <label for="empphone" id="edit-label">Phone</label>
                <input type="text" id="empphone" name="phone">
            </div>

            <div class="form-group">
                <label for="empaddress" id="edit-label">Address</label>
                <input type="text" id="empaddress" name="address">
            </div>

            <?php if (!empty($error)) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer;">Cancel</button>

            <input type="submit" value="Add" style="background-color:#1d5488; color:white; width:140px;height: 45px;
    font-size:large;
    text-align:center;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;cursor:pointer; margin-left:5%;">
        </form>
    </section>
    <script>
        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Managers.php';
        });
    </script>
</body>

</html>