<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'customer') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");
$customer_id = $_SESSION['user_id'];

// Fetch customer username
$sql_customer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_customer);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch previous submissions with photos
$sql = "SELECT month, pulses_customer, rice_customer, mustard_oil_customer, potato_customer, soyabean_customer, 
               pulses_dealer, rice_dealer, mustard_oil_dealer, potato_dealer, soyabean_dealer,
               customer_image_pulses, customer_image_rice, customer_image_oil, customer_image_potato, customer_image_soyabean 
        FROM distribution 
        WHERE customer_id = ? AND pulses_customer IS NOT NULL 
        ORDER BY month ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// Get the latest month where customer has submitted
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
    <title><?php echo htmlspecialchars($customer['username']); ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .photo-grid img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin: 4px;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg relative">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($customer['username']); ?> Dashboard</h2>
        
        <!-- Button to submit next month -->
        <a href="customer_submit.php?month=<?php echo $next_month; ?>" class="absolute top-6 right-6 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Submit Month <?php echo $next_month; ?></a>

        <!-- Previous Submissions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $result->data_seek(0); // Reset result pointer
            while ($submission = $result->fetch_assoc()):
                $customer_data = [
                    $submission['pulses_customer'],
                    $submission['rice_customer'],
                    $submission['mustard_oil_customer'],
                    $submission['potato_customer'],
                    $submission['soyabean_customer']
                ];
                $dealer_data = [
                    $submission['pulses_dealer'],
                    $submission['rice_dealer'],
                    $submission['mustard_oil_dealer'],
                    $submission['potato_dealer'],
                    $submission['soyabean_dealer']
                ];
                $border_class = 'border-gray-400'; // Default grey
                if ($submission['pulses_dealer'] !== null) { // Dealer has submitted
                    $match = true;
                    for ($i = 0; $i < 5; $i++) {
                        if ($customer_data[$i] != $dealer_data[$i]) {
                            $match = false;
                            break;
                        }
                    }
                    $border_class = $match ? 'border-green-500' : 'border-red-500';
                }
            ?>
                <div class="bg-gray-200 p-4 rounded-lg shadow border-2 <?php echo $border_class; ?>">
                    <h3 class="text-lg font-semibold mb-2">Month <?php echo $submission['month']; ?></h3>
                    <p>Pulses: <?php echo $submission['pulses_customer']; ?> kg</p>
                    <p>Rice: <?php echo $submission['rice_customer']; ?> kg</p>
                    <p>Mustard Oil: <?php echo $submission['mustard_oil_customer']; ?> L</p>
                    <p>Potato: <?php echo $submission['potato_customer']; ?> kg</p>
                    <p>Soyabean: <?php echo $submission['soyabean_customer']; ?> kg</p>
                    <div class="photo-grid mt-2">
                        <?php if ($submission['customer_image_pulses']): ?>
                            <img src="<?php echo htmlspecialchars($submission['customer_image_pulses']); ?>" alt="Pulses Photo">
                        <?php endif; ?>
                        <?php if ($submission['customer_image_rice']): ?>
                            <img src="<?php echo htmlspecialchars($submission['customer_image_rice']); ?>" alt="Rice Photo">
                        <?php endif; ?>
                        <?php if ($submission['customer_image_oil']): ?>
                            <img src="<?php echo htmlspecialchars($submission['customer_image_oil']); ?>" alt="Oil Photo">
                        <?php endif; ?>
                        <?php if ($submission['customer_image_potato']): ?>
                            <img src="<?php echo htmlspecialchars($submission['customer_image_potato']); ?>" alt="Potato Photo">
                        <?php endif; ?>
                        <?php if ($submission['customer_image_soyabean']): ?>
                            <img src="<?php echo htmlspecialchars($submission['customer_image_soyabean']); ?>" alt="Soyabean Photo">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <p class="col-span-full text-center text-gray-500">No submissions yet.</p>
            <?php endif; ?>
        </div>

        <a href="logout.php" class="mt-6 inline-block bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>