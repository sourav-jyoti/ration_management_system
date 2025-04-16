<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Validate dealer_id parameter
if (!isset($_GET['dealer_id']) || empty($_GET['dealer_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$dealer_id = (int)$_GET['dealer_id'];
$conn = new mysqli("localhost", "root", "", "food_management");

// Fetch dealer info
$sql_dealer = "SELECT username FROM users WHERE id = ? AND designation = 'dealer'";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();

// Redirect if dealer not found
if (!$dealer) {
    header("Location: admin_dashboard.php");
    exit();
}

// Initialize default values for allocations
$allocated = [
    'pulses' => 0,
    'rice' => 0,
    'mustard_oil' => 0,
    'potato' => 0,
    'soyabean' => 0
];

// Fetch allocations
$sql_alloc = "SELECT SUM(pulses) as pulses, SUM(rice) as rice, SUM(mustard_oil) as mustard_oil, 
              SUM(potato) as potato, SUM(soyabean) as soyabean 
              FROM allocations WHERE dealer_id = ?";
$stmt = $conn->prepare($sql_alloc);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$alloc_result = $stmt->get_result()->fetch_assoc();

// Merge with defaults, ensuring no null values
if ($alloc_result) {
    foreach ($allocated as $key => $value) {
        $allocated[$key] = $alloc_result[$key] ?? 0;
    }
}

// Initialize default values for inventory
$inventory = [
    'pulses' => 0,
    'rice' => 0,
    'mustard_oil' => 0,
    'potato' => 0,
    'soyabean' => 0
];

// Fetch inventory
$sql_inv = "SELECT pulses, rice, mustard_oil, potato, soyabean FROM inventory WHERE user_id = ?";
$stmt = $conn->prepare($sql_inv);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$inv_result = $stmt->get_result()->fetch_assoc();

// Merge with defaults, ensuring no null values
if ($inv_result) {
    foreach ($inventory as $key => $value) {
        $inventory[$key] = $inv_result[$key] ?? 0;
    }
}

// Get customer count with null check
$sql_count = "SELECT COUNT(*) as customer_count FROM users WHERE dealer_id = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$count_result = $stmt->get_result()->fetch_assoc();
$customer_count = $count_result ? ($count_result['customer_count'] ?? 0) : 0;

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
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .customer-card {
            transition: all 0.2s;
        }
        .customer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            <a href="admin_dashboard.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center mr-2">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dealers
            </a>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Dealer Info Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($dealer['username']); ?></h2>
                    <p class="text-gray-600 mt-1">Dealer Management Dashboard</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-store text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Allocation Stats -->
            <div class="bg-white p-6 rounded-lg shadow-lg stat-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Total Quantity Allocated</h3>
                        <p class="text-gray-600 text-sm">Historical allocation data</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2">
                        <i class="fas fa-boxes-stacked text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Pulses</span>
                        <span class="font-medium text-gray-800"><?php echo $allocated['pulses'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Rice</span>
                        <span class="font-medium text-gray-800"><?php echo $allocated['rice'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Mustard Oil</span>
                        <span class="font-medium text-gray-800"><?php echo $allocated['mustard_oil'] ?? 0; ?> L</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Potato</span>
                        <span class="font-medium text-gray-800"><?php echo $allocated['potato'] ?? 0; ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Soyabean</span>
                        <span class="font-medium text-gray-800"><?php echo $allocated['soyabean'] ?? 0; ?> kg</span>
                    </div>
                </div>
            </div>

            <!-- Current Inventory -->
            <div class="bg-white p-6 rounded-lg shadow-lg stat-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Current Inventory
                            <?php if ($has_shortage): ?>
                                <span class="text-red-600 text-sm ml-2">(Shortage Detected)</span>
                            <?php endif; ?>
                        </h3>
                        <p class="text-gray-600 text-sm">Available stock levels</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-2">
                        <i class="fas fa-warehouse text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Pulses</span>
                        <span class="font-medium <?php echo ($inventory['pulses'] < $thresholds['pulses']) ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $inventory['pulses'] ?? 0; ?> kg
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Rice</span>
                        <span class="font-medium <?php echo ($inventory['rice'] < $thresholds['rice']) ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $inventory['rice'] ?? 0; ?> kg
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Mustard Oil</span>
                        <span class="font-medium <?php echo ($inventory['mustard_oil'] < $thresholds['mustard_oil']) ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $inventory['mustard_oil'] ?? 0; ?> L
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Potato</span>
                        <span class="font-medium <?php echo ($inventory['potato'] < $thresholds['potato']) ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $inventory['potato'] ?? 0; ?> kg
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Soyabean</span>
                        <span class="font-medium <?php echo ($inventory['soyabean'] < $thresholds['soyabean']) ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $inventory['soyabean'] ?? 0; ?> kg
                        </span>
                    </div>
                </div>
                <?php if ($has_shortage): ?>
                    <div class="mt-4">
                        <a href="admin_allocate.php?dealer_id=<?php echo $dealer_id; ?>" 
                           class="inline-flex items-center justify-center w-full bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg hover:bg-yellow-200 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Allocate More Items
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customers Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Customers</h3>
                    <p class="text-gray-600 mt-1">Total Customers: <?php echo $customer_count; ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-2">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <?php $has_shortage_customer = isset($shortage_ids[$customer['id']]); ?>
                    <a href="admin_customer.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer['id']; ?>" 
                       class="customer-card block p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border <?php echo $has_shortage_customer ? 'border-red-300 hover:border-red-400' : 'border-gray-200 hover:border-blue-300'; ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($customer['username']); ?></h4>
                                    <p class="text-sm text-gray-600">ID: <?php echo $customer['id']; ?></p>
                                </div>
                            </div>
                            <?php if ($has_shortage_customer): ?>
                                <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-sm">Shortage</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>