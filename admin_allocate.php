<?php
session_start();
ob_start(); // Start output buffering

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "food_management");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dealer_id = (int)$_POST['dealer_id'];
    $pulses = (float)$_POST['pulses'];
    $rice = (float)$_POST['rice'];
    $mustard_oil = (float)$_POST['mustard_oil'];
    $potato = (float)$_POST['potato'];
    $soyabean = (float)$_POST['soyabean'];

    // Insert into allocations
    $sql = "INSERT INTO allocations (dealer_id, pulses, rice, mustard_oil, potato, soyabean) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iddddd", $dealer_id, $pulses, $rice, $mustard_oil, $potato, $soyabean);
    $stmt->execute();

    // Update inventory
    $sql = "UPDATE inventory SET pulses = pulses + ?, rice = rice + ?, mustard_oil = mustard_oil + ?, potato = potato + ?, soyabean = soyabean + ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddddi", $pulses, $rice, $mustard_oil, $potato, $soyabean, $dealer_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    // Redirect to avoid headers issue
    header("Location: admin_dealer.php?dealer_id=" . $dealer_id);
    exit();
}

// Fetch dealer username
$dealer_id = $_GET['dealer_id'];
$sql_dealer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Allocate to <?php echo htmlspecialchars($dealer['username']); ?></title>
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
        .form-card {
            transition: all 0.2s ease;
        }
        .form-card:hover {
            transform: translateY(-2px);
        }
        .input-group {
            transition: all 0.2s;
        }
        .input-group:hover {
            transform: translateX(5px);
        }
        .input-field {
            transition: all 0.2s;
        }
        .input-field:focus {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <header class="header">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-100 p-2 rounded-full">
                <i class="fas fa-user-shield text-blue-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="text-sm text-gray-600">Allocate Items to Dealer</p>
            </div>
        </div>
        <a href="admin_dealer.php?dealer_id=<?php echo $dealer_id; ?>" 
           class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dealer
        </a>
    </header>

    <div class="max-w-4xl mx-auto px-4">
        <!-- Allocation Form Card -->
        <div class="bg-white p-6 rounded-lg shadow-lg form-card">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-boxes-stacked text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Allocate Items</h2>
                        <p class="text-gray-600">Dealer: <?php echo htmlspecialchars($dealer['username']); ?></p>
                    </div>
                </div>
            </div>

            <form action="admin_allocate.php" method="POST" class="space-y-6">
                <input type="hidden" name="dealer_id" value="<?php echo $dealer_id; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pulses Input -->
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-seedling text-green-600 mr-2"></i>
                            Pulses (kg)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="pulses" 
                                   class="input-field w-full p-3 pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   required>
                            <span class="absolute right-3 top-3 text-gray-400">kg</span>
                        </div>
                    </div>

                    <!-- Rice Input -->
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-bowl-rice text-amber-600 mr-2"></i>
                            Rice (kg)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="rice" 
                                   class="input-field w-full p-3 pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                                   required>
                            <span class="absolute right-3 top-3 text-gray-400">kg</span>
                        </div>
                    </div>

                    <!-- Mustard Oil Input -->
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-oil-can text-yellow-600 mr-2"></i>
                            Mustard Oil (liters)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="mustard_oil" 
                                   class="input-field w-full p-3 pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" 
                                   required>
                            <span class="absolute right-3 top-3 text-gray-400">L</span>
                        </div>
                    </div>

                    <!-- Potato Input -->
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-apple-whole text-orange-600 mr-2"></i>
                            Potato (kg)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="potato" 
                                   class="input-field w-full p-3 pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                                   required>
                            <span class="absolute right-3 top-3 text-gray-400">kg</span>
                        </div>
                    </div>

                    <!-- Soyabean Input -->
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-leaf text-green-600 mr-2"></i>
                            Soyabean (kg)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="soyabean" 
                                   class="input-field w-full p-3 pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   required>
                            <span class="absolute right-3 top-3 text-gray-400">kg</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-8">
                    <button type="submit" 
                            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Allocate Items
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
ob_end_flush(); // Flush output buffer
?>