<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT id, password, designation, dealer_id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($password === $row['password']) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['designation'] = $row['designation'];
        if ($row['designation'] === 'customer') {
            $_SESSION['dealer_id'] = $row['dealer_id'];
        }
        if ($row['designation'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($row['designation'] === 'dealer') {
            header("Location: dealer_dashboard.php");
        } else {
            header("Location: customer_dashboard.php"); // Updated to customer_dashboard.php
        }
    } else {
        echo "Incorrect password!";
    }
} else {
    echo "User not found!";
}

$stmt->close();
$conn->close();
?>