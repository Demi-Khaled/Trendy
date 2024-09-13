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

// Check if form is submitted for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['idemp'];
    $username = $_POST['useremp'];
    $password = $_POST['passwordemp'];
    $job = $_POST['job'];
    $name = $_POST['empname'];
    $email = $_POST['empemail'];
    $phone = $_POST['empphone'];
    $address = $_POST['empaddress'];

    // Update query
    if (!empty($password)) {
        $sql = "UPDATE employee SET useremp=?, passwordemp=?, job=?, empname=?, empemail=?, empphone=?, empaddress=? WHERE idemp=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $username, $password, $job, $name, $email, $phone, $address, $id);
    } else {
        $sql = "UPDATE employee SET useremp=?, job=?, empname=?, empemail=?, empphone=?, empaddress=? WHERE idemp=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $username, $job, $name, $email, $phone, $address, $id);
    }
    if ($stmt->execute()) {
        header("Location: Employee.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// Fetch employee data for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM employee WHERE idemp=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Edit Employee</title>
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

    .form-group select {
        border: 1px solid #1d5488;
        border-radius: 25px;
        width: 220px;
        height: 50px;
        padding: 10px;
        margin: 20px 0px 0px 20px;
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
    <h1 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:white;">
        <span style="color:red;">UPDATE</span> Employee Data
    </h1>

    <form method="post" action="edit_employee.php" class="form-container" onsubmit="return validateForm()">
        <input type="hidden" name="idemp" value="<?php echo $employee['idemp']; ?>">

        <div class="form-group">
            <label for="useremp">Username</label>
            <input type="text" id="useremp" name="useremp" value="<?php echo $employee['useremp']; ?>">
        </div>

        <div class="form-group">
            <label for="emppassword">Password</label>
            <input type="password" id="emppassword" name="passwordemp" value="<?php echo $employee['passwordemp']; ?>">
        </div>

        <div class="form-group">
            <label for="job">Job</label>
            <select name="job" id="job">
                <option value="Cashier" <?php echo $employee['job'] == 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                <option value="Sales" <?php echo $employee['job'] == 'Sales' ? 'selected' : ''; ?>>Sales</option>
            </select>
        </div>

        <div class="form-group">
            <label for="empname">Name</label>
            <input type="text" id="empname" name="empname" value="<?php echo $employee['empname']; ?>">
        </div>

        <div class="form-group">
            <label for="empemail">Email</label>
            <input type="email" id="empemail" name="empemail" value="<?php echo $employee['empemail']; ?>">
        </div>

        <div class="form-group">
            <label for="empphone">Phone</label>
            <input type="text" id="empphone" name="empphone" value="<?php echo $employee['empphone']; ?>">
        </div>

        <div class="form-group">
            <label for="empaddress">Address</label>
            <input type="text" id="empaddress" name="empaddress" value="<?php echo $employee['empaddress']; ?>">
        </div>
        <div id="error-message"></div>
        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Cancel</button>

        <input type="submit" value="Update" style="background-color: #1d5488; color: white; height:45px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-color: transparent; border-radius: 25px; cursor: pointer; width:140px; margin-left:5%;">
    </form>
    <script>
        function validateForm() {
            var username = document.getElementById('useremp').value;
            var password = document.getElementById('emppassword').value;
            var job = document.getElementById('job').value;
            var name = document.getElementById('empname').value;
            var email = document.getElementById('empemail').value;
            var phone = document.getElementById('empphone').value;
            var address = document.getElementById('empaddress').value;
            var errorMessage = '';

            if (username === '' || password === '' || job === '' || name === '' || email === '' || phone === '' ||
                address === '') {
                errorMessage = 'Please fill in all fields.';
            }

            if (errorMessage !== '') {
                document.getElementById('error-message').textContent = errorMessage;
                return false;
            }
            return true;
        }

        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Employee.php';
        });
    </script>
</body>

</html>