<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");
$dealer_id = $_SESSION['user_id'];

// Fetch dealer username
$sql_dealer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();

// Fetch customers under this dealer
$sql = "SELECT id, username FROM users WHERE dealer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dealer['username']); ?> Dashboard</title>
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
        .customer-card {
            transition: all 0.2s ease;
        }
        .customer-card:hover {
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
                <p class="text-sm text-gray-600">Dealer Dashboard</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
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
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Customers</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $result->num_rows; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Your ID</p>
                        <h3 class="text-2xl font-bold text-gray-800">#<?php echo $_SESSION['user_id']; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-id-badge text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Role</p>
                        <h3 class="text-2xl font-bold text-gray-800">Dealer</h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Your Customers</h2>
                    <p class="text-gray-600 mt-1">Manage and view customer details</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php 
                $result->data_seek(0); // Reset result pointer
                while ($row = $result->fetch_assoc()): 
                ?>
                    <a href="dealer_customer.php?customer_id=<?php echo $row['id']; ?>" 
                       class="customer-card block p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:border-blue-300">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 p-2 rounded-full">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['username']); ?></h3>
                                <p class="text-sm text-gray-600">ID: <?php echo $row['id']; ?></p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <?php if ($result->num_rows == 0): ?>
                <div class="text-center py-8">
                    <div class="bg-gray-100 rounded-full p-3 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-users-slash text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-gray-500 font-medium">No customers found</h3>
                    <p class="text-gray-400 text-sm">You don't have any customers assigned yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>