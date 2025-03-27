<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_management");

if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_SESSION['user_id'];
$customer_id = $_POST['customer_id'];
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
    // Update only dealer fields if row exists
    $sql = "UPDATE distribution 
            SET pulses_dealer = ?, rice_dealer = ?, mustard_oil_dealer = ?, potato_dealer = ?, soyabean_dealer = ?, 
                dealer_image_pulses = ?, dealer_image_rice = ?, dealer_image_oil = ?, dealer_image_potato = ?, dealer_image_soyabean = ?
            WHERE dealer_id = ? AND customer_id = ? AND month = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddddsssssiii", $pulses, $rice, $mustard_oil, $potato, $soyabean, $image_paths['pulses'], $image_paths['rice'], $image_paths['mustard_oil'], $image_paths['potato'], $image_paths['soyabean'], $dealer_id, $customer_id, $month);
} else {
    // Insert only dealer data without customer fields
    $sql = "INSERT INTO distribution (dealer_id, customer_id, month, pulses_dealer, rice_dealer, mustard_oil_dealer, potato_dealer, soyabean_dealer, dealer_image_pulses, dealer_image_rice, dealer_image_oil, dealer_image_potato, dealer_image_soyabean)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiddddddssss", $dealer_id, $customer_id, $month, $pulses, $rice, $mustard_oil, $potato, $soyabean, $image_paths['pulses'], $image_paths['rice'], $image_paths['mustard_oil'], $image_paths['potato'], $image_paths['soyabean']);
}

if ($stmt->execute()) {
    $sql = "UPDATE inventory SET pulses = pulses - ?, rice = rice - ?, mustard_oil = mustard_oil - ?, potato = potato - ?, soyabean = soyabean - ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddddi", $pulses, $rice, $mustard_oil, $potato, $soyabean, $dealer_id);
    $stmt->execute();
    header("Location: dealer_customer.php?customer_id=" . $customer_id);
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$stmt_check->close();
$conn->close();
?>