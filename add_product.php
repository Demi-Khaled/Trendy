<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
} ?>
<?php
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "trendy"; // Your database name
$port = 3307;


$error = ""; // Variable to store error message


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted for adding a new item
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemnumber = $_POST['itemnumber'];
    $itemname = $_POST['itemname'];
    $itemsize = $_POST['itemsize'];
    $itemmaterial = $_POST['itemmaterial'];
    $itemcolor = $_POST['itemcolor'];
    $itemstock = $_POST['itemstock'];
    $itemprice = $_POST['itemprice'];
    $itemdisc = $_POST['discount'];

    // Insert query
    if (!empty($itemnumber) && !empty($itemname) && !empty($itemsize) && !empty($itemmaterial) && !empty($itemcolor) && !empty($itemstock) && !empty($itemprice)) {
        // Insert query
        $sql = "INSERT INTO inventory (itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice, discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssidi", $itemnumber, $itemsize,  $itemmaterial, $itemname, $itemcolor, $itemstock, $itemprice, $itemdisc);

        if ($stmt->execute()) {
            // Redirect to inventory.php after successful insertion
            header("Location: Inventory.php");
            exit();
        } else {
            $error = "Error adding record: " . $conn->error;
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
    <title>Add new product</title>
    <style>
        body {
            background-color: #092D65;
            color: aliceblue;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h1><span style="color:red;">ADD</span> New Product</h1>
    <form action="add_product.php" method="post" class="form-container">
        <div class="form-group">
            <label for="itemnumber">Item ID</label>
            <input type="text" id="itemnumber" name="itemnumber">
        </div>
        <div class="form-group">
            <label for="itemname">Item Name</label>
            <input type="text" id="itemname" name="itemname">
        </div>
        <div class="form-group">
            <label for="itemmaterial">Material</label>
            <input type="text" id="itemmaterial" name="itemmaterial">
        </div>
        <div class="form-group">
            <label for="itemsize">Size</label>
            <input type="text" id="itemsize" name="itemsize">
        </div>
        <div class="form-group">
            <label for="itemcolor">Color</label>
            <input type="text" id="itemcolor" name="itemcolor">
        </div>
        <div class="form-group">
            <label for="itemstock">Quantity in Stock</label>
            <input type="text" id="itemstock" name="itemstock">
        </div>
        <div class="form-group">
            <label for="itemprice">Price per Item</label>
            <input type="number" id="itemprice" name="itemprice" min="1" step="1">
        </div>
        <div class="form-group">
            <label for="discount">Discount</label>
            <input type="number" id="discount" name="discount" min="0" max="100" step="1">
        </div>
        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <button id="cancelBtn" type="button" style="background-color: gray; color: black; width: 140px; height: 45px; border-radius: 25px; border-color: transparent; font-size: large; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; cursor:pointer; margin-left:5%;">Cancel</button>

        <input type="submit" value="Add" style="background-color:#1d5488; color:white; width:140px;height: 45px;
            font-size:large;
            text-align:center;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;cursor:pointer;margin-left:5%;">
    </form>
    <script>
        document.getElementById('cancelBtn').addEventListener('click', function() {
            window.location.href = 'inventory.php';
        });
    </script>
</body>

</html>