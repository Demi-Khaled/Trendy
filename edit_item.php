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


$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemnumber = $_POST['itemnumber'];
    $itemname = $_POST['itemname'];
    $itemmaterial = $_POST['itemmaterial'];
    $itemsize = $_POST['itemsize'];
    $itemcolor = $_POST['itemcolor'];
    $itemstock = $_POST['itemstock'];
    $itemprice = $_POST['itemprice'];
    $discount = $_POST['discount'];

    // Validate form fields
    if (!empty($itemnumber) && !empty($itemname) && !empty($itemmaterial) && !empty($itemsize) && !empty($itemcolor) && !empty($itemstock) && !empty($itemprice)) {
        // SQL query to update item data
        $sql = "UPDATE inventory SET itemname = ?, itemmaterial = ?, itemsize = ?, itemcolor = ?, itemstock = ?, itemprice = ?, discount = ? WHERE itemnumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssidi", $itemname, $itemmaterial, $itemsize, $itemcolor, $itemstock, $itemprice, $discount, $itemnumber);

        if ($stmt->execute()) {
            // Redirect to inventory.php after successful update
            header("Location: Inventory.php");
            exit();
        } else {
            $error = "Error updating record: " . $conn->error;
        }

        $stmt->close();
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Check if the item ID is set in the query string
if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // SQL query to retrieve item data based on item ID
    $sql = "SELECT itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice,discount FROM inventory WHERE itemnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemData = $result->fetch_assoc();
} else {
    // Redirect back to the inventory page if no item ID is provided
    header("Location: Inventory.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="style.css">
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
        color: red;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin-bottom: 20px;
    }
</style>


<body style="background-color: #092D65;">
    <h1 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:white;"><span style="color:red;">UPDATE</span>
        Item Data</h1>
    <div>
        <?php if ($itemData) : ?>
            <form action="edit_item.php?id=<?php echo htmlspecialchars($itemData['itemnumber']); ?>" method="post" name="itemnumber" class="form-container" value="<?php echo htmlspecialchars($itemData['itemnumber']); ?>">


                <div class="form-group">
                    <label for="edit-itemnumber">Item Number</label>
                    <input type="text" name="itemnumber" value="<?php echo htmlspecialchars($itemData['itemnumber']); ?>" readonly><br>
                </div>
                <div class="form-group">

                    <label for="edit-itemname">Item Name</label>
                    <input type="text" name="itemname" value="<?php echo htmlspecialchars($itemData['itemname']); ?>"><br>
                </div>

                <div class="form-group">
                    <label for="edit-itemmaterial">Material</label>
                    <input type="text" name="itemmaterial" value="<?php echo htmlspecialchars($itemData['itemmaterial']); ?>"><br>
                </div>

                <div class="form-group">

                    <label for="edit-itemsize">Size</label>
                    <input type="text" name="itemsize" value="<?php echo htmlspecialchars($itemData['itemsize']); ?>"><br>
                </div>

                <div class="form-group">
                    <label for="edit-itemcolor">Color</label>
                    <input type="text" name="itemcolor" value="<?php echo htmlspecialchars($itemData['itemcolor']); ?>"><br>
                </div>

                <div class="form-group">

                    <label for="edit-itemstock">Quantity in Stock</label>
                    <input type="text" name="itemstock" value="<?php echo htmlspecialchars($itemData['itemstock']); ?>"><br>
                </div>

                <div class="form-group">

                    <label for="edit-itemprice">Price per Item</label>
                    <input type="text" name="itemprice" value="<?php echo htmlspecialchars($itemData['itemprice']); ?>"><br>
                </div>
                <div class="form-group">
                    <label for="discount">Discount</label>
                    <input type="number" id="discount" name="discount" min="0" max="100" step="1" value="<?php echo htmlspecialchars($itemData['discount']); ?>">
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Cancel</button>

                <input type="submit" value="Update" style="background-color: #1d5488; color: white; height:45px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-color: transparent; border-radius: 25px; cursor: pointer; width:140px; margin-left:5%;">
            </form>
            <script>
                document.getElementById('cancelBtn').addEventListener('click', function() {
                    window.location.href = 'inventory.php';
                });
            </script>
        <?php else : ?>
            <p>Item not found.</p>
        <?php endif; ?>
    </div>
</body>

</html>