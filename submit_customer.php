<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_management");

if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'customer') {
    header("Location: login.html");
    exit();
}

$customer_id = $_SESSION['user_id'];
$dealer_id = $_SESSION['dealer_id'];
$month = (int)$_POST['month'];

$pulses = (float)$_POST['pulses'];
$rice = (float)$_POST['rice'];
$mustard_oil = (float)$_POST['mustard_oil'];
$potato = (float)$_POST['potato'];
$soyabean = (float)$_POST['soyabean'];

$upload_dir = "uploads/";
$fields = ['pulses', 'rice', 'mustard_oil', 'potato', 'soyabean'];
$image_paths = [];
foreach ($fields as $field) {
    $image_paths[$field] = !empty($_FILES[$field . '_image']['name']) ? $upload_dir . basename($_FILES[$field . '_image']['name']) : NULL;
    if ($image_paths[$field]) {
        move_uploaded_file($_FILES[$field . '_image']['tmp_name'], $image_paths[$field]);
    }
}

// Check if a row exists for this dealer, customer, and month
$sql_check = "SELECT id FROM distribution WHERE dealer_id = ? AND customer_id = ? AND month = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $dealer_id, $customer_id, $month);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // Update only customer fields if row exists
    $sql = "UPDATE distribution 
            SET pulses_customer = ?, rice_customer = ?, mustard_oil_customer = ?, potato_customer = ?, soyabean_customer = ?, 
                customer_image_pulses = ?, customer_image_rice = ?, customer_image_oil = ?, customer_image_potato = ?, customer_image_soyabean = ?
            WHERE dealer_id = ? AND customer_id = ? AND month = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddddsssssiii", $pulses, $rice, $mustard_oil, $potato, $soyabean, $image_paths['pulses'], $image_paths['rice'], $image_paths['mustard_oil'], $image_paths['potato'], $image_paths['soyabean'], $dealer_id, $customer_id, $month);
} else {
    // Insert only customer data without dealer fields
    $sql = "INSERT INTO distribution (dealer_id, customer_id, month, pulses_customer, rice_customer, mustard_oil_customer, potato_customer, soyabean_customer, customer_image_pulses, customer_image_rice, customer_image_oil, customer_image_potato, customer_image_soyabean) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiddddddssss", $dealer_id, $customer_id, $month, $pulses, $rice, $mustard_oil, $potato, $soyabean, $image_paths['pulses'], $image_paths['rice'], $image_paths['mustard_oil'], $image_paths['potato'], $image_paths['soyabean']);
}

if ($stmt->execute()) {
    header("Location: customer_dashboard.php");
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$stmt_check->close();
$conn->close();
?>