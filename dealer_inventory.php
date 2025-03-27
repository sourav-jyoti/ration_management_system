<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");
$dealer_id = $_SESSION['user_id'];

$sql = "SELECT pulses, rice, mustard_oil, potato, soyabean FROM inventory WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$result = $stmt->get_result();
$inventory = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Dealer Inventory</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>Pulses: <?php echo $inventory['pulses'] ?? 0; ?> kg</div>
            <div>Rice: <?php echo $inventory['rice'] ?? 0; ?> kg</div>
            <div>Mustard Oil: <?php echo $inventory['mustard_oil'] ?? 0; ?> liters</div>
            <div>Potato: <?php echo $inventory['potato'] ?? 0; ?> kg</div>
            <div>Soyabean: <?php echo $inventory['soyabean'] ?? 0; ?> kg</div>
        </div>
        <a href="dealer_dashboard.php" class="mt-6 inline-block text-blue-500">Back to Customers</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>