<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<?php
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

$inventoryData = array();

// SQL query to retrieve data (executed only once on page load)
$sql = "SELECT itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice, discount FROM inventory";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventoryData[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Orders</title>


    <script>
    function addProduct(itemnumber, itemname, itemprice, itemsize, itemcolor, itemmaterial, itemstock, discount) {
        const quantityInput = document.getElementById(`quantity-${itemnumber}`);
        const quantity = parseInt(quantityInput.value);

        if (quantity > itemstock) {
            alert("The stock is insufficient.");
            return;
        }

        if (quantity > 0) {
            const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
            let existingRow = null;

            const discountedPrice = itemprice - (itemprice * (discount / 100));
            const totalPrice = discountedPrice * quantity;

            // Check if the product already exists in the added products table
            for (let i = 0; i < addedProductsTable.rows.length; i++) {
                if (addedProductsTable.rows[i].cells[0].innerText == itemnumber) {
                    existingRow = addedProductsTable.rows[i];
                    break;
                }
            }

            if (existingRow) {
                // Update the quantity and total price if the product exists
                const existingQuantity = parseInt(existingRow.cells[6].innerText);
                const newQuantity = existingQuantity + quantity;
                existingRow.cells[6].innerText = newQuantity;
                const newTotalPrice = discountedPrice * newQuantity;
                existingRow.cells[9].innerText = newTotalPrice.toFixed(2) + ' L.E';
            } else {
                // Add a new row if the product does not exist
                const newRow = addedProductsTable.insertRow();
                newRow.insertCell(0).innerText = itemnumber;
                newRow.insertCell(1).innerText = itemname;
                newRow.insertCell(2).innerText = itemsize;
                newRow.insertCell(3).innerText = itemcolor;
                newRow.insertCell(4).innerText = itemmaterial;
                newRow.insertCell(5).innerText = itemprice + ' L.E';
                newRow.insertCell(6).innerText = quantity;
                newRow.insertCell(7).innerText = discount + '%';
                newRow.insertCell(8).innerText = discountedPrice.toFixed(2) + ' L.E';
                newRow.insertCell(9).innerText = totalPrice.toFixed(2) + ' L.E';
                newRow.insertCell(10).innerHTML =
                    `<button onclick="deleteProduct('${itemnumber}', ${itemprice}, ${discount})" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:60px; border-radius:25px; border-color:transparent;cursor:pointer;">Delete</button>`;
            }

            // Update item stock in the inventory table
            const currentStock = parseInt(document.getElementById(`stock-${itemnumber}`).innerText);
            const newStock = currentStock - quantity;
            document.getElementById(`stock-${itemnumber}`).innerText = newStock;

            updateTotalPrice();
            // Reset quantity input to 1 after adding product
            quantityInput.value = 1;
        } else {
            alert("Please enter a valid quantity.");
            return;
        }
    }

    function deleteProduct(itemnumber, itemprice, discount) {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
        const quantityToDelete = 1; // Decrement by 1

        // Find the row to delete
        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            if (addedProductsTable.rows[i].cells[0].innerText == itemnumber) {
                const existingQuantity = parseInt(addedProductsTable.rows[i].cells[6].innerText);
                const newQuantity = existingQuantity - quantityToDelete;

                if (newQuantity <= 0) {
                    // Remove the row if the quantity is zero or less
                    addedProductsTable.deleteRow(i);
                } else {
                    // Update the quantity and total price if the quantity is more than zero
                    addedProductsTable.rows[i].cells[6].innerText = newQuantity;
                    const discountedPrice = itemprice - (itemprice * (discount / 100));
                    const newTotalPrice = discountedPrice * newQuantity;
                    addedProductsTable.rows[i].cells[9].innerText = newTotalPrice.toFixed(2) + ' L.E';
                }

                // Update item stock in the inventory table
                const currentStock = parseInt(document.getElementById(`stock-${itemnumber}`).innerText);
                const newStock = currentStock + quantityToDelete;
                document.getElementById(`stock-${itemnumber}`).innerText = newStock;

                break;
            }
        }

        updateTotalPrice();
    }

    function updateTotalPrice() {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
        let totalPrice = 0;

        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            const totalPriceText = addedProductsTable.rows[i].cells[9].innerText.replace(' L.E', '');
            totalPrice += parseFloat(totalPriceText);
        }

        document.getElementById('total-price').innerText = totalPrice.toFixed(2) + ' L.E';
    }

    function confirmOrder() {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
        const orderData = [];

        // Collect order data from the table
        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            const cells = addedProductsTable.rows[i].cells;
            const item = {
                itemnumber: cells[0].innerText,
                itemname: cells[1].innerText,
                itemsize: cells[2].innerText,
                itemcolor: cells[3].innerText,
                itemmaterial: cells[4].innerText,
                itemprice: parseFloat(cells[5].innerText.replace(' L.E', '')),
                quantity: parseInt(cells[6].innerText),
                discount: parseFloat(cells[7].innerText.replace('%', '')),
                priceAfterDiscount: parseFloat(cells[8].innerText.replace(' L.E', '')),
                totalPrice: parseFloat(cells[9].innerText.replace(' L.E', ''))
            };
            orderData.push(item);
        }

        // Convert order data to JSON and set it to the hidden input
        document.getElementById('order-data').value = JSON.stringify(orderData);

        // Submit the form
        document.getElementById('confirm-order-form').submit();
    }
    </script>


</head>

<body style="background-color: #092D65;">
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php">Dashboard</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php" style="color:#1d5488;">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Managers.php">Managers</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav>
    </div>
    <input type="search" placeholder="Search" id="search">
    <table id="inventoryTable">
        <thead>
            <tr>
                <th style="color: black;text-align: center;">ID</th>
                <th style="color: black;text-align: center;">Name</th>
                <th style="color: black;text-align: center;">Size</th>
                <th style="color: black;text-align: center;">Color</th>
                <th style="color: black;text-align: center;">Material</th>
                <th style="color: black;text-align: center;">Price</th>
                <th style="color: black;text-align: center;">Discount</th>
                <th style="color: black;text-align: center;">Stock</th>
                <th style="color: black;text-align: center;">Quantity</th>
                <th style="color: black;text-align: center;">Add</th>
            </tr>
        </thead>
        <tbody style="color:#1d5488; font-weight:500;">
            <?php if (empty($inventoryData)) : ?>
            <tr>
                <td colspan="10" class="no-data"
                    style="text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: bolder; color: red; font-size: larger;">
                    The inventory is empty</td>
            </tr>
            <?php else : ?>
            <?php foreach ($inventoryData as $item) : ?>
            <tr>
                <td><?= $item['itemnumber'] ?></td>
                <td><?= $item['itemname'] ?></td>
                <td><?= $item['itemsize'] ?></td>
                <td><?= $item['itemcolor'] ?></td>
                <td><?= $item['itemmaterial'] ?></td>
                <td><?= $item['itemprice'] ?> <b>L.E</b></td>
                <td><?= $item['discount'] ?> <b>%</b></td>
                <td id="stock-<?= $item['itemnumber'] ?>"><?= $item['itemstock'] ?></td>
                <td>
                    <input type="number" id="quantity-<?= $item['itemnumber'] ?>" min="1" value="1"
                        style="border-radius:25px; border-color:transparent; color:white;background-color:#1d5488;">
                </td>
                <td>
                    <button
                        onclick="addProduct('<?= $item['itemnumber'] ?>', '<?= $item['itemname'] ?>', <?= $item['itemprice'] ?>, '<?= $item['itemsize'] ?>', '<?= $item['itemcolor'] ?>', '<?= $item['itemmaterial'] ?>', <?= $item['itemstock'] ?>, <?= $item['discount'] ?>)"
                        style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:50px; border-radius:25px; border-color:transparent; cursor: pointer;">Add</button>
                </td>

            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <table id="added-products">
        <thead>
            <tr>
                <th style="color: black;text-align: center;">ID</th>
                <th style="color: black;text-align: center;">Name</th>
                <th style="color: black;text-align: center;">Size</th>
                <th style="color: black;text-align: center;">Color</th>
                <th style="color: black;text-align: center;">Material</th>
                <th style="color: black;text-align: center;">Price</th>
                <th style="color: black;text-align: center;">Quantity</th>
                <th style="color: black;text-align: center;">Discount</th>
                <th style="color: black;text-align: center;">Price After Discount</th>
                <th style="color: black;text-align: center;">Total Price</th>
                <th style="color: black;text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody style="color:#1d5488; font-weight:500;">
        </tbody>
        <tfoot style="font-weight:bolder;">
            <tr>
                <td colspan="10" style="text-align:right"><b>Total Price</b></td>
                <td id="total-price"><b>0.00 L.E</b></td>
            </tr>
        </tfoot>
    </table>

    <form id="confirm-order-form" action="confirm_order.php" method="post">
        <input type="hidden" name="orderData" id="order-data">
        <button type="button" onclick="confirmOrder()" style="
        background-color: black;
        color: white;
        height: 35px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: large;
        padding: 0px 0px 3px 0px;
        border-color: transparent;
        margin:1% 0% 0% 90%;
        border-radius: 25px;
        width: 150px;
        cursor: pointer;
    ">Confirm
            order</button>
    </form>
    <script>
    document.getElementById('search').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var table = document.getElementById('inventoryTable');
        var rows = table.getElementsByTagName('tr');

        for (var i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
            var cells = rows[i].getElementsByTagName('td');
            var match = false;

            for (var j = 0; j < cells.length; j++) {
                if (cells[j].innerText.toLowerCase().indexOf(searchValue) > -1) {
                    match = true;
                    break;
                }
            }

            if (match) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
    </script>
</body>

</html>