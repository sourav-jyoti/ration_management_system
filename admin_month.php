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

$fixed = ['pulses' => 1, 'rice' => 5, 'mustard_oil' => 2, 'potato' => 3, 'soyabean' => 2];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($names['customer_name']); ?> Month <?php echo $month; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            margin-top: 16px;
        }
        .photo-grid img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: transform 0.2s;
        }
        .photo-grid img:hover {
            transform: scale(1.05);
        }
        .comparison-card {
            transition: transform 0.2s;
        }
        .comparison-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="header">
        <div class="flex items-center">
            <i class="fas fa-user-shield text-blue-600 text-2xl mr-3"></i>
            <h1 class="text-xl font-bold">Admin Dashboard</h1>
        </div>
        <div class="flex items-center">
            <a href="admin_customer.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer_id; ?>" 
               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center mr-2">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Customer
            </a>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Month Info Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($names['customer_name']); ?></h2>
                    <div class="flex items-center mt-2">
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">Month <?php echo $month; ?></span>
                        <span class="mx-2 text-gray-400">•</span>
                        <span class="text-gray-600">Dealer: <?php echo htmlspecialchars($names['dealer_name']); ?></span>
                    </div>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-calendar-alt text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Comparison Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Report -->
            <div class="bg-white p-6 rounded-lg shadow-lg comparison-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Customer Report</h3>
                        <p class="text-gray-600 text-sm">Items reported by customer</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2">
                        <i class="fas fa-user text-green-600 text-xl"></i>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['pulses_customer'] != $fixed['pulses'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Pulses</span>
                        <span class="font-medium "><?php echo $distribution['pulses_customer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['rice_customer'] != $fixed['rice'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Rice</span>
                        <span class="font-medium "><?php echo $distribution['rice_customer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['mustard_oil_customer'] != $fixed['mustard_oil'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Mustard Oil</span>
                        <span class="font-medium "><?php echo $distribution['mustard_oil_customer'] ?? 0; ?> L</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['potato_customer'] != $fixed['potato'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Potato</span>
                        <span class="font-medium "><?php echo $distribution['potato_customer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['soyabean_customer'] != $fixed['soyabean'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Soyabean</span>
                        <span class="font-medium "><?php echo $distribution['soyabean_customer'] ?? 0; ?> kg</span>
                    </div>
                </div>

                <?php if (!empty($distribution['customer_image_pulses']) || !empty($distribution['customer_image_rice']) || 
                          !empty($distribution['customer_image_oil']) || !empty($distribution['customer_image_potato']) || 
                          !empty($distribution['customer_image_soyabean'])): ?>
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Submitted Photos</h4>
                        <div class="photo-grid">
                            <?php if ($distribution['customer_image_pulses']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['customer_image_pulses']); ?>" alt="Pulses Photo" title="Pulses">
                            <?php endif; ?>
                            <?php if ($distribution['customer_image_rice']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['customer_image_rice']); ?>" alt="Rice Photo" title="Rice">
                            <?php endif; ?>
                            <?php if ($distribution['customer_image_oil']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['customer_image_oil']); ?>" alt="Oil Photo" title="Mustard Oil">
                            <?php endif; ?>
                            <?php if ($distribution['customer_image_potato']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['customer_image_potato']); ?>" alt="Potato Photo" title="Potato">
                            <?php endif; ?>
                            <?php if ($distribution['customer_image_soyabean']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['customer_image_soyabean']); ?>" alt="Soyabean Photo" title="Soyabean">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dealer Report -->
            <div class="bg-white p-6 rounded-lg shadow-lg comparison-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Dealer Report</h3>
                        <p class="text-gray-600 text-sm">Items reported by dealer</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-2">
                        <i class="fas fa-store text-purple-600 text-xl"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['pulses_dealer'] != $fixed['pulses'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Pulses</span>
                        <span class="font-medium "><?php echo $distribution['pulses_dealer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['rice_dealer'] != $fixed['rice'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Rice</span>
                        <span class="font-medium "><?php echo $distribution['rice_dealer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['mustard_oil_dealer'] != $fixed['mustard_oil'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Mustard Oil</span>
                        <span class="font-medium "><?php echo $distribution['mustard_oil_dealer'] ?? 0; ?> L</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['potato_dealer'] != $fixed['potato'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Potato</span>
                        <span class="font-medium "><?php echo $distribution['potato_dealer'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg <?php echo $distribution['soyabean_dealer'] != $fixed['soyabean'] ? 'text-red-600' : 'text-gray-800'; ?>">
                        <span class="">Soyabean</span>
                        <span class="font-medium "><?php echo $distribution['soyabean_dealer'] ?? 0; ?> kg</span>
                    </div>
                </div>

                <?php if (!empty($distribution['dealer_image_pulses']) || !empty($distribution['dealer_image_rice']) || 
                          !empty($distribution['dealer_image_oil']) || !empty($distribution['dealer_image_potato']) || 
                          !empty($distribution['dealer_image_soyabean'])): ?>
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Submitted Photos</h4>
                        <div class="photo-grid">
                            <?php if ($distribution['dealer_image_pulses']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['dealer_image_pulses']); ?>" alt="Pulses Photo" title="Pulses">
                            <?php endif; ?>
                            <?php if ($distribution['dealer_image_rice']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['dealer_image_rice']); ?>" alt="Rice Photo" title="Rice">
                            <?php endif; ?>
                            <?php if ($distribution['dealer_image_oil']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['dealer_image_oil']); ?>" alt="Oil Photo" title="Mustard Oil">
                            <?php endif; ?>
                            <?php if ($distribution['dealer_image_potato']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['dealer_image_potato']); ?>" alt="Potato Photo" title="Potato">
                            <?php endif; ?>
                            <?php if ($distribution['dealer_image_soyabean']): ?>
                                <img src="<?php echo htmlspecialchars($distribution['dealer_image_soyabean']); ?>" alt="Soyabean Photo" title="Soyabean">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>