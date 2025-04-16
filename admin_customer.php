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
        .month-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .month-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
        <div>
            <a href="admin_dealer.php?dealer_id=<?php echo $dealer_id; ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center mr-2 inline-flex">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dealer
            </a>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center inline-flex">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Customer Info Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($names['customer_name']); ?></h2>
                    <p class="text-gray-600">Customer of <?php echo htmlspecialchars($names['dealer_name']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-user text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Submissions -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Monthly Submissions</h3>
                    <p class="text-gray-600 mt-1">Track monthly distribution records</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php while ($submission = $result->fetch_assoc()): ?>
                    <?php
                    $shortage = ($submission['pulses_customer'] != $fixed['pulses'] || 
                                $submission['rice_customer'] != $fixed['rice'] || 
                                $submission['mustard_oil_customer'] != $fixed['mustard_oil'] || 
                                $submission['potato_customer'] != $fixed['potato'] || 
                                $submission['soyabean_customer'] != $fixed['soyabean']);
                    ?>
                    <a href="admin_month.php?dealer_id=<?php echo $dealer_id; ?>&customer_id=<?php echo $customer_id; ?>&month=<?php echo $submission['month']; ?>" 
                       class="month-card block bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-lg border <?php echo $shortage ? 'border-red-300 hover:border-red-400' : 'border-gray-200 hover:border-blue-300'; ?>">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Month <?php echo $submission['month']; ?></h3>
                            <?php if ($shortage): ?>
                                <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-sm">Error</span>
                            <?php endif; ?>
                        </div>
                        
                    </a>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <div class="col-span-full flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-500 text-center">No submissions yet.</p>
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