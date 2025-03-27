<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_GET['dealer_id'];
$customer_id = $_GET['customer_id'];
$month = $_GET['month'];
$conn = new mysqli("localhost", "root", "", "food_management");

// Fetch customer and dealer names
$sql_names = "SELECT (SELECT username FROM users WHERE id = ?) as dealer_name, (SELECT username FROM users WHERE id = ?) as customer_name";
$stmt = $conn->prepare($sql_names);
$stmt->bind_param("ii", $dealer_id, $customer_id);
$stmt->execute();
$names = $stmt->get_result()->fetch_assoc();

// Fetch distribution data with photos
$sql = "SELECT pulses_dealer, rice_dealer, mustard_oil_dealer, potato_dealer, soyabean_dealer, 
               pulses_customer, rice_customer, mustard_oil_customer, potato_customer, soyabean_customer,
               customer_image_pulses, customer_image_rice, customer_image_oil, customer_image_potato, customer_image_soyabean,
               dealer_image_pulses, dealer_image_rice, dealer_image_oil, dealer_image_potato, dealer_image_soyabean 
        FROM distribution 
        WHERE dealer_id = ? AND customer_id = ? AND month = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $dealer_id, $customer_id, $month);
$stmt->execute();
$distribution = $stmt->get_result()->fetch_assoc() ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($names['customer_name']); ?> Month <?php echo $month; ?></title>
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
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($names['customer_name']); ?> - Month <?php echo $month; ?></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-200 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Customer Reported</h3>
                <p>Pulses: <?php echo $distribution['pulses_customer'] ?? 0; ?> kg</p>
                <p>Rice: <?php echo $distribution['rice_customer'] ?? 0; ?> kg</p>
                <p>Mustard Oil: <?php echo $distribution['mustard_oil_customer'] ?? 0; ?> L</p>
                <p>Potato: <?php echo $distribution['potato_customer'] ?? 0; ?> kg</p>
                <p>Soyabean: <?php echo $distribution['soyabean_customer'] ?? 0; ?> kg</p>
                <div class="photo-grid mt-2">
                    <?php if ($distribution['customer_image_pulses']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['customer_image_pulses']); ?>" alt="Pulses Photo">
                    <?php endif; ?>
                    <?php if ($distribution['customer_image_rice']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['customer_image_rice']); ?>" alt="Rice Photo">
                    <?php endif; ?>
                    <?php if ($distribution['customer_image_oil']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['customer_image_oil']); ?>" alt="Oil Photo">
                    <?php endif; ?>
                    <?php if ($distribution['customer_image_potato']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['customer_image_potato']); ?>" alt="Potato Photo">
                    <?php endif; ?>
                    <?php if ($distribution['customer_image_soyabean']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['customer_image_soyabean']); ?>" alt="Soyabean Photo">
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-gray-200 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Dealer Reported</h3>
                <p>Pulses: <?php echo $distribution['pulses_dealer'] ?? 0; ?> kg</p>
                <p>Rice: <?php echo $distribution['rice_dealer'] ?? 0; ?> kg</p>
                <p>Mustard Oil: <?php echo $distribution['mustard_oil_dealer'] ?? 0; ?> L</p>
                <p>Potato: <?php echo $distribution['potato_dealer'] ?? 0; ?> kg</p>
                <p>Soyabean: <?php echo $distribution['soyabean_dealer'] ?? 0; ?> kg</p>
                <div class="photo-grid mt-2">
                    <?php if ($distribution['dealer_image_pulses']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['dealer_image_pulses']); ?>" alt="Pulses Photo">
                    <?php endif; ?>
                    <?php if ($distribution['dealer_image_rice']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['dealer_image_rice']); ?>" alt="Rice Photo">
                    <?php endif; ?>
                    <?php if ($distribution['dealer_image_oil']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['dealer_image_oil']); ?>" alt="Oil Photo">
                    <?php endif; ?>
                    <?php if ($distribution['dealer_image_potato']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['dealer_image_potato']); ?>" alt="Potato Photo">
                    <?php endif; ?>
                    <?php if ($distribution['dealer_image_soyabean']): ?>
                        <img src="<?php echo htmlspecialchars($distribution['dealer_image_soyabean']); ?>" alt="Soyabean Photo">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <a href="admin_customer.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer_id; ?>" class="mt-6 inline-block text-blue-500">Back to Months</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>