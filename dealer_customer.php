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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header {
            background-color: #f8fafc;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .month-card {
            transition: all 0.2s ease;
        }
        .month-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <header class="header">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-100 p-2 rounded-full">
                <i class="fas fa-store text-blue-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($dealer['username']); ?></h1>
                <p class="text-sm text-gray-600">Customer Management</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="dealer_dashboard.php" 
               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Customers
            </a>
            <a href="dealer_inventory.php" 
               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <i class="fas fa-warehouse mr-2"></i>
                View Inventory
            </a>
            <a href="logout.php" 
               class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Customer Info Card -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-user text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($customer['username']); ?></h2>
                        <p class="text-gray-600">Customer ID: <?php echo $customer_id; ?></p>
                    </div>
                </div>
                <a href="dealer_submit.php?customer_id=<?php echo $customer_id; ?>&month=<?php echo $next_month; ?>" 
                   class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Submit Month <?php echo $next_month; ?>
                </a>
            </div>
        </div>

        <!-- Submissions Grid -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Monthly Submissions</h3>
                    <p class="text-gray-600 mt-1">Track distribution records</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                    $match = true;
                    $status_class = 'border-gray-200';
                    $status_bg = 'bg-gray-50';
                    $status_text = 'Pending';
                    $status_icon = 'clock';
                    $status_color = 'text-gray-600';

                    if ($submission['pulses_customer'] !== null) {
                        $match = true;
                        for ($i = 0; $i < 5; $i++) {
                            if ($dealer_data[$i] != $customer_data[$i]) {
                                $match = false;
                                break;
                            }
                        }
                        if ($match) {
                            $status_class = 'border-green-200';
                            $status_bg = 'bg-green-50';
                            $status_text = 'Matched with customer';
                            $status_icon = 'check-circle';
                            $status_color = 'text-green-600';
                        } else {
                            $status_class = 'border-red-200';
                            $status_bg = 'bg-red-50';
                            $status_text = 'Mismatch';
                            $status_icon = 'exclamation-circle';
                            $status_color = 'text-red-600';
                        }
                    }
                ?>
                    <div class="month-card rounded-lg border-2 <?php echo $status_class; ?> <?php echo $status_bg; ?> overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-800">Month <?php echo $submission['month']; ?></h4>
                                <div class="flex items-center <?php echo $status_color; ?>">
                                    <i class="fas fa-<?php echo $status_icon; ?> mr-2"></i>
                                    <span class="text-sm font-medium"><?php echo $status_text; ?></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600"><i class="fas fa-seedling mr-2"></i>Pulses</span>
                                    <span class="font-medium"><?php echo $submission['pulses_dealer']; ?> kg</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600"><i class="fas fa-bowl-rice mr-2"></i>Rice</span>
                                    <span class="font-medium"><?php echo $submission['rice_dealer']; ?> kg</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600"><i class="fas fa-oil-can mr-2"></i>Mustard Oil</span>
                                    <span class="font-medium"><?php echo $submission['mustard_oil_dealer']; ?> L</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600"><i class="fas fa-apple-whole mr-2"></i>Potato</span>
                                    <span class="font-medium"><?php echo $submission['potato_dealer']; ?> kg</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600"><i class="fas fa-leaf mr-2"></i>Soyabean</span>
                                    <span class="font-medium"><?php echo $submission['soyabean_dealer']; ?> kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($result->num_rows == 0): ?>
                    <div class="col-span-full text-center py-8">
                        <div class="bg-gray-100 rounded-full p-3 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-gray-500 font-medium">No submissions yet</h3>
                        <p class="text-gray-400 text-sm">Start by submitting your first month's distribution.</p>
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