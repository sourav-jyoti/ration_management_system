<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_SESSION['user_id'];
$customer_id = $_GET['customer_id'];
$conn = new mysqli("localhost", "root", "", "food_management");

// Fetch dealer username
$sql_dealer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();

// Fetch customer name
$sql_customer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_customer);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch submissions where dealer has entered data
$sql = "SELECT month, pulses_dealer, rice_dealer, mustard_oil_dealer, potato_dealer, soyabean_dealer, 
               pulses_customer, rice_customer, mustard_oil_customer, potato_customer, soyabean_customer 
        FROM distribution 
        WHERE dealer_id = ? AND customer_id = ? AND pulses_dealer IS NOT NULL 
        ORDER BY month ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $dealer_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// Get the latest month
$latest_month = 0;
while ($row = $result->fetch_assoc()) {
    if ($row['month'] > $latest_month) {
        $latest_month = $row['month'];
    }
}
$next_month = $latest_month + 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dealer['username']); ?> Dashboard - <?php echo htmlspecialchars($customer['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg relative">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($dealer['username']); ?> Dashboard - <?php echo htmlspecialchars($customer['username']); ?></h2>
        
        <!-- Button to submit next month -->
        <a href="dealer_submit.php?customer_id=<?php echo $customer_id; ?>&month=<?php echo $next_month; ?>" class="absolute top-6 right-6 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Submit Month <?php echo $next_month; ?></a>

        <!-- Previous Submissions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $result->data_seek(0); // Reset result pointer
            while ($submission = $result->fetch_assoc()):
                $dealer_data = [
                    $submission['pulses_dealer'],
                    $submission['rice_dealer'],
                    $submission['mustard_oil_dealer'],
                    $submission['potato_dealer'],
                    $submission['soyabean_dealer']
                ];
                $customer_data = [
                    $submission['pulses_customer'],
                    $submission['rice_customer'],
                    $submission['mustard_oil_customer'],
                    $submission['potato_customer'],
                    $submission['soyabean_customer']
                ];
                $border_class = 'border-gray-400'; // Default grey
                if ($submission['pulses_customer'] !== null) { // Customer has submitted
                    $match = true;
                    for ($i = 0; $i < 5; $i++) {
                        if ($dealer_data[$i] != $customer_data[$i]) {
                            $match = false;
                            break;
                        }
                    }
                    $border_class = $match ? 'border-green-500' : 'border-red-500';
                }
            ?>
                <div class="bg-gray-200 p-4 rounded-lg shadow border-2 <?php echo $border_class; ?>">
                    <h3 class="text-lg font-semibold mb-2">Month <?php echo $submission['month']; ?></h3>
                    <p>Pulses: <?php echo $submission['pulses_dealer']; ?> kg</p>
                    <p>Rice: <?php echo $submission['rice_dealer']; ?> kg</p>
                    <p>Mustard Oil: <?php echo $submission['mustard_oil_dealer']; ?> L</p>
                    <p>Potato: <?php echo $submission['potato_dealer']; ?> kg</p>
                    <p>Soyabean: <?php echo $submission['soyabean_dealer']; ?> kg</p>
                </div>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <p class="col-span-full text-center text-gray-500">No submissions yet.</p>
            <?php endif; ?>
        </div>

        <a href="dealer_dashboard.php" class="mt-6 inline-block text-blue-500">Back to Customers</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>