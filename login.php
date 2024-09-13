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
    if ($role == 'Manager') {
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
            if ($role == 'Manager') {
                header('Location: Home page.php'); // Redirect to management home page
            } elseif ($role == 'Employee') {
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
            document.getElementById("managementForm").style.display = role === 'Manager' ? 'block' : 'none';
            document.getElementById("employeeForm").style.display = role === 'Employee' ? 'block' : 'none';
            clearFields(); // Clear fields when switching forms
        }

        // JavaScript function to clear form fields
        function clearFields() {
            document.querySelectorAll("input[type=text], input[type=password]").forEach(input => input.value = "");
        }
    </script>
</head>


<body style="background-color: #092D65;">
    <div class="input-box">
        <button type="button" onclick="showForm('Manager')">Manager</button>
        <button type="button" onclick="showForm('Employee')">Employee</button>

        <div id="managementForm" style="display:none;">
            <form action="login.php" method="post">
                <input type="hidden" name="role" value="Manager">
                <img src="user(1).png" alt="user" id="user">
                <h2>Management Login</h2>
                <input id="admin" type="text" name="admin" placeholder="Admin" autofocus required>
                <br> <br>
                <input id="password" type="password" name="password" placeholder="Password" required>
                <br><br>
                <input type="submit" id="signbtn" name="submit" value="Sign in">
                <?php if (!empty($error_message)) : ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </form>
        </div>

        <div id="employeeForm" style="display:none;">
            <form action="login.php" method="post">
                <input type="hidden" name="role" value="Employee">
                <img src="user(1).png" alt="user" id="user">
                <h2>Employee Login</h2>
                <input id="admin" type="text" name="admin" placeholder="Username" autofocus required>
                <br> <br>
                <input id="password" type="password" name="password" placeholder="Password" required>
                <br><br>
                <input type="submit" id="signbtn" name="submit" value="Sign in">
                <?php if (!empty($error_message)) : ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Show Management form by default
        showForm('Manager');
    </script>
</body>

</html>