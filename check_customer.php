<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $customerName = $input['customerName'];
    $customerPhoneNumber = $input['customerPhoneNumber'];

    // Database connection
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=trendy', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if customer exists
    $stmt = $pdo->prepare("SELECT customerid FROM customer WHERE customername = :customername AND customerphone = :customerphone");
    $stmt->execute(['customername' => $customerName, 'customerphone' => $customerPhoneNumber]);
    $customer = $stmt->fetch();

    if (!$customer) {
        // Insert new customer
        $stmt = $pdo->prepare("INSERT INTO customer (customername, customerphone) VALUES (:customername, :customerphone)");
        $stmt->execute(['customername' => $customerName, 'customerphone' => $customerPhoneNumber]);
        $customerId = $pdo->lastInsertId();
        echo json_encode(['exists' => false, 'customerId' => $customerId]);
    } else {
        echo json_encode(['exists' => true, 'customerId' => $customer['customerid']]);
    }
}
