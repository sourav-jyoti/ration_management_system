<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");
$dealer_id = $_SESSION['user_id'];

// Fetch dealer username
$sql_dealer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();

// Fetch customers under this dealer
$sql = "SELECT id, username FROM users WHERE dealer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dealer['username']); ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($dealer['username']); ?> Dashboard - Customers</h2>
        <div class="grid grid-cols-1 gap-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <a href="dealer_customer.php?customer_id=<?php echo $row['id']; ?>" class="p-4 bg-gray-200 rounded hover:bg-gray-300">
                    <?php echo htmlspecialchars($row['username']); ?>
                </a>
            <?php endwhile; ?>
        </div>
        <a href="dealer_inventory.php" class="mt-6 inline-block bg-blue-500 text-white p-2 rounded hover:bg-blue-600">View Inventory</a>
        <a href="logout.php" class="mt-6 inline-block bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>