<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "food_management");

$sql = "SELECT id, username FROM users WHERE designation = 'dealer'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dealers</title>
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
        .dealer-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dealer-card:hover {
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
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Stats Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-sm font-medium">Total Dealers</p>
                            <p class="text-2xl font-bold text-blue-800"><?php echo $result->num_rows; ?></p>
                        </div>
                        <i class="fas fa-users text-blue-400 text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Manage Dealers</h2>
                    <p class="text-gray-600 mt-1">View and monitor all registered dealers</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php while ($dealer = $result->fetch_assoc()): ?>
                    <a href="admin_dealer.php?dealer_id=<?php echo $dealer['id']; ?>" 
                       class="dealer-card block p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:border-blue-300">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($dealer['username']); ?></h3>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <div class="col-span-full flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <i class="fas fa-users-slash text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-500 text-center">No dealers found in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>