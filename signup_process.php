<?php
$conn = new mysqli("localhost", "root", "", "food_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];
$designation = $_POST['designation'];
$email = $_POST['email'];

$dealer_id = ($designation === 'customer') ? $_POST['dealer_id'] : NULL;

$sql = "INSERT INTO users (username, password, designation,email,dealer_id) VALUES (?, ?, ?, ?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $username, $password, $designation,$email,$dealer_id);

if ($stmt->execute()) {
    if ($designation === 'dealer') {
        $dealer_id = $conn->insert_id;
        $sql = "INSERT INTO inventory (user_id, pulses, rice, mustard_oil, potato, soyabean) VALUES (?, 0, 0, 0, 0, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
    }
    header("Location: login.html");
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>