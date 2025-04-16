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
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
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
            border-color: #3b82f6;
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
                <i class="fas fa-user text-blue-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($customer['username']); ?></h1>
                <p class="text-sm text-gray-600">Customer Dashboard</p>
            </div>
        </div>
        <a href="logout.php" 
           class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
            <i class="fas fa-sign-out-alt mr-2"></i>
            Logout
        </a>
    </header>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Customer Info Card -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Monthly Submissions</h2>
                        <p class="text-gray-600">Track your distribution records</p>
                    </div>
                </div>
                <a href="customer_submit.php?month=<?php echo $next_month; ?>" 
                   class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Submit Month <?php echo $next_month; ?>
                </a>
            </div>
        </div>

        <!-- Submissions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                
                $status_class = 'border-gray-200';
                $status_bg = 'bg-gray-50';
                $status_text = 'Pending Dealer';
                $status_icon = 'clock';
                $status_color = 'text-gray-600';

                if ($submission['pulses_dealer'] !== null) {
                    $match = true;
                    for ($i = 0; $i < 5; $i++) {
                        if ($customer_data[$i] != $dealer_data[$i]) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        $status_class = 'border-green-200';
                        $status_bg = 'bg-green-50';
                        $status_text = 'matched with dealer';
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
                                <span class="font-medium"><?php echo $submission['pulses_customer']; ?> kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><i class="fa-thin fa-bowl-rice mr-2"></i>Rice</span>
                                <span class="font-medium"><?php echo $submission['rice_customer']; ?> kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><i class="fas fa-oil-can mr-2"></i>Mustard Oil</span>
                                <span class="font-medium"><?php echo $submission['mustard_oil_customer']; ?> L</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><i class="fas fa-apple-whole mr-2"></i>Potato</span>
                                <span class="font-medium"><?php echo $submission['potato_customer']; ?> kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><i class="fas fa-leaf mr-2"></i>Soyabean</span>
                                <span class="font-medium"><?php echo $submission['soyabean_customer']; ?> kg</span>
                            </div>
                        </div>

                        <?php if ($submission['customer_image_pulses'] || 
                                  $submission['customer_image_rice'] || 
                                  $submission['customer_image_oil'] || 
                                  $submission['customer_image_potato'] || 
                                  $submission['customer_image_soyabean']): ?>
                            <div class="mt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-camera mr-2"></i>Submitted Photos
                                </h5>
                                <div class="photo-grid">
                                    <?php if ($submission['customer_image_pulses']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['customer_image_pulses']); ?>" 
                                             alt="Pulses Photo" title="Pulses">
                                    <?php endif; ?>
                                    <?php if ($submission['customer_image_rice']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['customer_image_rice']); ?>" 
                                             alt="Rice Photo" title="Rice">
                                    <?php endif; ?>
                                    <?php if ($submission['customer_image_oil']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['customer_image_oil']); ?>" 
                                             alt="Oil Photo" title="Mustard Oil">
                                    <?php endif; ?>
                                    <?php if ($submission['customer_image_potato']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['customer_image_potato']); ?>" 
                                             alt="Potato Photo" title="Potato">
                                    <?php endif; ?>
                                    <?php if ($submission['customer_image_soyabean']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['customer_image_soyabean']); ?>" 
                                             alt="Soyabean Photo" title="Soyabean">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
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
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>