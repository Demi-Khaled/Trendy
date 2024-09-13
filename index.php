<?php
session_start();

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
$error_message = '';

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['admin'];
    $password = $_POST['password'];
    $role = $_POST['role']; // This will hold either 'Management' or 'Employee'

    // Validate inputs (you may want to add more validation)
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Check which role is selected and perform appropriate query
    if ($role == 'Management') {
        $sql = "SELECT idadmin, username, password FROM admin WHERE username = '$username' AND password = '$password'";
    } elseif ($role == 'Employee') {
        $sql = "SELECT idemp, useremp, passwordemp, empname, job, empemail, empphone, empaddress FROM employee WHERE useremp = '$username' AND passwordemp = '$password'";
    } else {
        $error_message = "Please select a role.";
    }

    // Perform the query
    if (isset($sql)) {
        $result = $conn->query($sql);

        if ($result && $result->num_rows == 1) {
            // Login successful
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $username; // Store username in session
            $_SESSION['role'] = $role; // Store role in session


            // Redirect based on role
            if ($role == 'Management') {

                $idemp = $row['idemp'];
                $empname = $row['empname'];
                $logdate = date('Y-m-d H:i:s');

                // Insert login log
                $log_sql = "INSERT INTO logs (idemp, empname,job, logdate) VALUES ('$idemp', '$empname','$role' ,'$logdate')";
                $conn->query($log_sql);

                header('Location: Home page.php'); // Redirect to management home page
            } elseif ($role == 'Employee') {
                $idemp = $row['idemp'];
                $empname = $row['empname'];
                $logdate = date('Y-m-d H:i:s');

                // Insert login log
                $log_sql = "INSERT INTO logs (idemp, empname,job, logdate) VALUES ('$idemp', '$empname','$role' ,'$logdate')";
                $conn->query($log_sql);

                header('Location: Home page.php'); // Redirect to employee home page
            }
            exit;
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="log in.css">
    <script>
    // JavaScript function to toggle form visibility
    function showForm(role) {
        document.getElementById("managementForm").style.display = role === 'Management' ? 'block' : 'none';
        document.getElementById("employeeForm").style.display = role === 'Employee' ? 'block' : 'none';
        clearFields(); // Clear fields when switching forms
    }

    // JavaScript function to clear form fields
    function clearFields() {
        document.querySelectorAll("input[type=text], input[type=password]").forEach(input => input.value = "");
    }
    </script>
</head>

<style>
body {
    background-color: #092D65;
}
</style>
<style>
button {
    color: white;
}

button:hover {
    color: #1d5488;
    transition: 0.3 s;
}

button:active {
    color: #1d5488;
}
</style>

<body>
    <div class="input-box" style="width:auto;height:510px; text-align:center;">
        <section>
            <button type="button" id="mng" onclick="showForm('Management')"
                style="border-color: transparent; margin-top:40px;background-color:#1d5488;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size:larger;color: white;border-radius: 25px;height: 30px;width: 150px;cursor:pointer; margin-right:15px;">Management</butt>

                <button type="button" id="emp" onclick="showForm('Employee')" style="border-color: transparent;background-color: #1d5488;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:larger;
    color: white;border-radius: 25px;height:30px;width: 150px;cursor:pointer;">Employee</button>

        </section>


        <div id="managementForm" style="display:none; background-color:transparent;">
            <form action="index.php" method="post">
                <input type="hidden" name="role" value="Management">
                <img src="manager.png" alt="user" id="user">
                <h2>Management Login</h2>
                <input id="admin" type="text" name="admin" placeholder="Admin"
                    style="border-color:#1763a9; background-color:aliceblue;border-radius: 25px; color:#1763a9;"
                    autofocus required>
                <br> <br>
                <input id="password" type="password" name="password" placeholder="Password"
                    style="border-color:#1763a9; background-color:aliceblue;border-radius: 25px;color:#1763a9;"
                    required>
                <br><br>
                <input type="submit" id="signbtn" name="submit" value="Sign in" style="cursor:pointer;">
                <?php if (!empty($error_message)) : ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </form>
        </div>

        <div id="employeeForm"
            style="display:none; border-radius: 25px; height:auto; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:transparent;">
            <form action="index.php" method="post">
                <input type="hidden" name="role" value="Employee">
                <img src="user(1).png" alt="user" id="user">
                <h2>Employee Login</h2>
                <input id="admin" type="text" name="admin" placeholder="Username"
                    style="border-color:#1763a9; background-color:aliceblue;border-radius: 25px;color:#1763a9;"
                    autofocus required>
                <br> <br>
                <input id="password" type="password" name="password" placeholder="Password"
                    style="border-color:#1763a9; background-color:aliceblue;border-radius: 25px;color:#1763a9;"
                    required>
                <br><br>
                <input type="submit" id="signbtn" name="submit" value="Sign in" style="cursor:pointer;">
                <?php if (!empty($error_message)) : ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
    // Show Management form by default
    showForm('Management');
    </script>
</body>

</html>