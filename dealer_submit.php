<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_SESSION['user_id'];
$customer_id = $_GET['customer_id'];
$month = $_GET['month'];

// Fetch customer name for display
$conn = new mysqli("localhost", "root", "", "food_management");
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer - Submit for <?php echo htmlspecialchars($customer['username']); ?> Month <?php echo $month; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .header {
            background-color: #f8fafc;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <header class="header">
        <div class="flex items-center">
            <h1 class="text-xl font-bold">Submit Distribution Details</h1>
        </div>
        <div>
            <a href="dealer_customer.php?customer_id=<?php echo $customer_id; ?>" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600 mr-2">Back to Customer</a>
            <a href="dealer_inventory.php" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600 mr-2">View Inventory</a>
            <a href="logout.php" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
        </div>
    </header>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Submit for <?php echo htmlspecialchars($customer['username']); ?> - Month <?php echo $month; ?></h2>
        <form action="submit_dealer.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Pulses (kg)</label>
                    <input type="number" step="0.01" name="pulses" class="w-full p-2 border rounded" required>
                    <input type="file" name="pulses_image" class="mt-2">
                </div>
                <div>
                    <label>Rice (kg)</label>
                    <input type="number" step="0.01" name="rice" class="w-full p-2 border rounded" required>
                    <input type="file" name="rice_image" class="mt-2">
                </div>
                <div>
                    <label>Mustard Oil (liters)</label>
                    <input type="number" step="0.01" name="mustard_oil" class="w-full p-2 border rounded" required>
                    <input type="file" name="oil_image" class="mt-2">
                </div>
                <div>
                    <label>Potato (kg)</label>
                    <input type="number" step="0.01" name="potato" class="w-full p-2 border rounded" required>
                    <input type="file" name="potato_image" class="mt-2">
                </div>
                <div>
                    <label>Soyabean (kg)</label>
                    <input type="number" step="0.01" name="soyabean" class="w-full p-2 border rounded" required>
                    <input type="file" name="soyabean_image" class="mt-2">
                </div>
            </div>
            <button type="submit" class="mt-6 w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Submit</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>