<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_GET['dealer_id'];
$conn = new mysqli("localhost", "root", "", "food_management");

$sql_dealer = "SELECT username FROM users WHERE id = ? AND designation = 'dealer'";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();

$sql_alloc = "SELECT SUM(pulses) as pulses, SUM(rice) as rice, SUM(mustard_oil) as mustard_oil, SUM(potato) as potato, SUM(soyabean) as soyabean 
             FROM allocations WHERE dealer_id = ?";
$stmt = $conn->prepare($sql_alloc);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$allocated = $stmt->get_result()->fetch_assoc();

$sql_inv = "SELECT pulses, rice, mustard_oil, potato, soyabean FROM inventory WHERE user_id = ?";
$stmt = $conn->prepare($sql_inv);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_assoc();

$sql_count = "SELECT COUNT(*) as customer_count FROM users WHERE dealer_id = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$customer_count = $stmt->get_result()->fetch_assoc()['customer_count'];

$sql_customers = "SELECT u.id, u.username 
                  FROM users u 
                  LEFT JOIN distribution d ON u.id = d.customer_id 
                  WHERE u.dealer_id = ? 
                  GROUP BY u.id, u.username 
                  HAVING SUM(CASE WHEN d.pulses_customer < 1 OR d.rice_customer < 5 OR d.mustard_oil_customer < 2 OR d.potato_customer < 3 OR d.soyabean_customer < 2 THEN 1 ELSE 0 END) > 0";
$stmt = $conn->prepare($sql_customers);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$shortage_customers = $stmt->get_result();

$shortage_ids = [];
while ($row = $shortage_customers->fetch_assoc()) {
    $shortage_ids[$row['id']] = true;
}

$sql_all_customers = "SELECT id, username FROM users WHERE dealer_id = ?";
$stmt = $conn->prepare($sql_all_customers);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$customers = $stmt->get_result();

$thresholds = ['pulses' => 20, 'rice' => 100, 'mustard_oil' => 40, 'potato' => 60, 'soyabean' => 40];
$has_shortage = ($inventory['pulses'] < $thresholds['pulses'] || $inventory['rice'] < $thresholds['rice'] || 
                 $inventory['mustard_oil'] < $thresholds['mustard_oil'] || $inventory['potato'] < $thresholds['potato'] || 
                 $inventory['soyabean'] < $thresholds['soyabean']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($dealer['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg relative">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($dealer['username']); ?> Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-200 p-4 rounded-lg shadow border-2 border-gray-400">
                <h3 class="text-lg font-semibold mb-2">Total Quantity Allocated</h3>
                <p>Pulses: <?php echo $allocated['pulses'] ?? 0; ?> kg</p>
                <p>Rice: <?php echo $allocated['rice'] ?? 0; ?> kg</p>
                <p>Mustard Oil: <?php echo $allocated['mustard_oil'] ?? 0; ?> L</p>
                <p>Potato: <?php echo $allocated['potato'] ?? 0; ?> kg</p>
                <p>Soyabean: <?php echo $allocated['soyabean'] ?? 0; ?> kg</p>
            </div>
            <div class="bg-gray-200 p-4 rounded-lg shadow border-2 <?php echo $has_shortage ? 'border-red-500' : 'border-gray-400'; ?>">
                <h3 class="text-lg font-semibold mb-2">
                    Available Quantity
                    <?php if ($has_shortage): ?>
                        <a href="admin_allocate.php?dealer_id=<?php echo $dealer_id; ?>" class="float-right text-yellow-600 hover:underline">Items Required</a>
                        <span class="float-right text-red-700 mr-2">Shortage</span>
                    <?php endif; ?>
                </h3>
                <p>Pulses: <?php echo $inventory['pulses'] ?? 0; ?> kg</p>
                <p>Rice: <?php echo $inventory['rice'] ?? 0; ?> kg</p>
                <p>Mustard Oil: <?php echo $inventory['mustard_oil'] ?? 0; ?> L</p>
                <p>Potato: <?php echo $inventory['potato'] ?? 0; ?> kg</p>
                <p>Soyabean: <?php echo $inventory['soyabean'] ?? 0; ?> kg</p>
                <p>Total Customers: <?php echo $customer_count; ?></p>
            </div>
        </div>

        <h3 class="text-lg font-semibold mb-4">Customers</h3>
        <div class="grid grid-cols-1 gap-4">
            <?php while ($customer = $customers->fetch_assoc()): ?>
                <?php $has_shortage_customer = isset($shortage_ids[$customer['id']]); ?>
                <a href="admin_customer.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer['id']; ?>" 
                   class="p-4 bg-gray-200 rounded hover:bg-gray-300 border-2 <?php echo $has_shortage_customer ? 'border-red-500' : 'border-gray-400'; ?>">
                    <?php echo htmlspecialchars($customer['username']); ?>
                    <?php if ($has_shortage_customer): ?><span class="text-red-700"> - Shortage</span><?php endif; ?>
                </a>
            <?php endwhile; ?>
        </div>

        <a href="admin_dashboard.php" class="mt-6 inline-block text-blue-500">Back to Dealers</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>