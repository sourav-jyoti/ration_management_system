<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_GET['dealer_id'];
$customer_id = $_GET['customer_id'];
$conn = new mysqli("localhost", "root", "", "food_management");

// Fetch customer and dealer names
$sql_names = "SELECT (SELECT username FROM users WHERE id = ?) as dealer_name, (SELECT username FROM users WHERE id = ?) as customer_name";
$stmt = $conn->prepare($sql_names);
$stmt->bind_param("ii", $dealer_id, $customer_id);
$stmt->execute();
$names = $stmt->get_result()->fetch_assoc();

// Fetch customer submissions
$sql = "SELECT month, pulses_customer, rice_customer, mustard_oil_customer, potato_customer, soyabean_customer 
        FROM distribution 
        WHERE customer_id = ? AND pulses_customer IS NOT NULL 
        ORDER BY month ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$fixed = ['pulses' => 1, 'rice' => 5, 'mustard_oil' => 2, 'potato' => 3, 'soyabean' => 2];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($names['customer_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($names['customer_name']); ?> - Months</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php while ($submission = $result->fetch_assoc()): ?>
                <?php
                $shortage = ($submission['pulses_customer'] < $fixed['pulses'] || 
                             $submission['rice_customer'] < $fixed['rice'] || 
                             $submission['mustard_oil_customer'] < $fixed['mustard_oil'] || 
                             $submission['potato_customer'] < $fixed['potato'] || 
                             $submission['soyabean_customer'] < $fixed['soyabean']);
                $border_class = $shortage ? 'border-red-500' : 'border-gray-400';
                ?>
                <a href="admin_month.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer_id; ?>&month=<?php echo $submission['month']; ?>" 
                   class="bg-gray-200 p-4 rounded-lg shadow border-2 <?php echo $border_class; ?>">
                    <h3 class="text-lg font-semibold mb-2">Month <?php echo $submission['month']; ?></h3>
                    <p>Pulses: <?php echo $submission['pulses_customer']; ?> kg</p>
                    <p>Rice: <?php echo $submission['rice_customer']; ?> kg</p>
                    <p>Mustard Oil: <?php echo $submission['mustard_oil_customer']; ?> L</p>
                    <p>Potato: <?php echo $submission['potato_customer']; ?> kg</p>
                    <p>Soyabean: <?php echo $submission['soyabean_customer']; ?> kg</p>
                </a>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <p class="col-span-full text-center text-gray-500">No submissions yet.</p>
            <?php endif; ?>
        </div>
        <a href="admin_dealer.php?dealer_id=<?php echo $dealer_id; ?>" class="mt-6 inline-block text-blue-500">Back to Dealer</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>