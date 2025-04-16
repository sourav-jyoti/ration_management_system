<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'dealer') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");
$dealer_id = $_SESSION['user_id'];

$sql = "SELECT pulses, rice, mustard_oil, potato, soyabean FROM inventory WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$result = $stmt->get_result();
$inventory = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Inventory</title>
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
        .inventory-card {
            transition: transform 0.2s;
        }
        .inventory-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <header class="header">
        <div class="flex items-center">
            <h1 class="text-xl font-bold">Dealer Inventory</h1>
        </div>
        <div>
            <a href="dealer_dashboard.php" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600 mr-2">Back to Customers</a>
            <a href="logout.php" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto">

    <div class="bg-white p-6 rounded-lg shadow-lg mb-10">
            <h3 class="text-lg font-semibold mb-4 ">Quick Stats</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-600">Total Items</p>
                    <p class="text-2xl font-bold text-blue-800">5</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-600">Total Weight</p>
                    <p class="text-2xl font-bold text-green-800">
                        <?php 
                            $total_weight = ($inventory['pulses'] ?? 0) + ($inventory['rice'] ?? 0) + 
                                          ($inventory['potato'] ?? 0) + ($inventory['soyabean'] ?? 0);
                            echo $total_weight;
                        ?> kg
                    </p>
                </div>
                <div class=" bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg shadow-md border ">
                    <p class="text-sm text-amber-600">Total Liquid</p>
                    <p class="text-2xl font-bold text-amber-800"><?php echo $inventory['mustard_oil'] ?? 0; ?> L</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-2xl font-bold mb-2">Current Stock Overview</h2>
            <p class="text-gray-600 mb-6">Monitor your inventory levels</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Pulses Card -->
                <div class="inventory-card bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-md border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-seedling text-yellow-600 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Pulses</h3>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo $inventory['pulses'] ?? 0; ?> kg</span>
                    </div>
                </div>

                <!-- Rice Card -->
                <div class="inventory-card bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-md border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-bowl-rice text-green-600 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Rice</h3>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo $inventory['rice'] ?? 0; ?> kg</span>
                    </div>
                </div>

                <!-- Mustard Oil Card -->
                <div class="inventory-card bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg shadow-md border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-oil-can text-amber-600 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Mustard Oil</h3>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo $inventory['mustard_oil'] ?? 0; ?> L</span>
                    </div>
                </div>

                <!-- Potato Card -->
                <div class="inventory-card bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-md border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-apple-whole text-orange-600 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Potato</h3>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo $inventory['potato'] ?? 0; ?> kg</span>
                    </div>
                </div>

                <!-- Soyabean Card -->
                <div class="inventory-card bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-md border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-leaf text-lime-600 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-800">Soyabean</h3>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo $inventory['soyabean'] ?? 0; ?> kg</span>
                    </div>
                </div>
            </div>
        </div>


        
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>