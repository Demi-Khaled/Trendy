<?php

require 'access_control.php';
restrict_access(['Management']); // Only allow managers to access this page

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

// Check if form is submitted for adding a new employee
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['useremp'];
    $password = $_POST['emppassword'];
    $name = $_POST['empfname'];
    $job = isset($_POST['job']) ? $_POST['job'] : null;
    $email = $_POST['empemail'];
    $phone = $_POST['empphone'];
    $address = $_POST['empaddress'];

    // Insert query
    if (!empty($username) && !empty($password) && !empty($name) && !empty($job) && !empty($email) && !empty($phone) && !empty($address)) {
        $sql = "INSERT INTO employee (useremp, passwordemp, empname, job, empemail, empphone, empaddress) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $username, $password, $name, $job, $email, $phone, $address);

        if ($stmt->execute()) {
            // Redirect to employee.php after successful insertion
            header("Location: Employee.php");
            exit();
        } else {
            echo "Error adding record: " . $conn->error;
        }

        $stmt->close();
    } else {
        $error = "Please fill in all required fields.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Add Employee</title>
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
    <h1 style="color: aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:30px 0px 0px 50px">
        <span style="color:red;">ADD</span> New Employee
    </h1>
    <form method="post" class="form-container" action="add_employee.php">

        <div class="form-group">
            <label for="useremp" id="edit-label">Username</label>
            <input type="text" id="useremp" name="useremp" value="">
        </div>

        <div class="form-group">
            <label for="emppassword" id="edit-label">Password</label>
            <input type="password" id="emppassword" name="emppassword">
        </div>

        <div class="form-group">
            <label for="empfname" id="edit-label">Name</label>
            <input type="text" id="empfname" name="empfname">
        </div>

        <div class="form-group">
            <label for="job" id="edit-label">Job</label>
            <select name="job">
                <option value="" disabled selected>Select job</option>
                <option value="Cashier">Cashier</option>
                <option value="Sales">Sales</option>
            </select>
        </div>
        <div class="form-group">
            <label for="empemail" id="edit-label">Email</label>
            <input type="email" id="empemail" name="empemail">
        </div>

        <div class="form-group">
            <label for="empphone" id="edit-label">Phone</label>
            <input type="text" id="empphone" name="empphone">
        </div>

        <div class="form-group">
            <label for="empaddress" id="edit-label">Address</label>
            <input type="text" id="empaddress" name="empaddress">
        </div>
        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Cancel</button>

        <input type="submit" value="Add" style="background-color:#1d5488; color:white; width:140px;height: 45px;
            font-size:large;
            text-align:center;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;cursor:pointer;">


        <br><br>
    </form>
    <script>
        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'Employee.php';
        });
    </script>
</body>

</html>