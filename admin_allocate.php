<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['designation'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$dealer_id = $_GET['dealer_id'];
$conn = new mysqli("localhost", "root", "", "food_management");

// Fetch dealer username
$sql_dealer = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_dealer);
$stmt->bind_param("i", $dealer_id);
$stmt->execute();
$dealer = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Allocate to <?php echo htmlspecialchars($dealer['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg border-2 border-gray-400">
        <h2 class="text-2xl font-bold mb-6">Allocate Items to <?php echo htmlspecialchars($dealer['username']); ?></h2>
        <form action="admin_allocate.php" method="POST">
            <input type="hidden" name="dealer_id" value="<?php echo $dealer_id; ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Pulses (kg)</label>
                    <input type="number" step="0.01" name="pulses" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Rice (kg)</label>
                    <input type="number" step="0.01" name="rice" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Mustard Oil (liters)</label>
                    <input type="number" step="0.01" name="mustard_oil" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Potato (kg)</label>
                    <input type="number" step="0.01" name="potato" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label>Soyabean (kg)</label>
                    <input type="number" step="0.01" name="soyabean" class="w-full p-2 border rounded" required>
                </div>
            </div>
            <button type="submit" class="mt-6 w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Submit</button>
        </form>
        <a href="admin_dealer.php?dealer_id=<?php echo $dealer_id; ?>" class="mt-4 inline-block text-blue-500">Back to Dealer</a>
    </div>

    <?php
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

        header("Location: admin_dealer.php?dealer_id=" . $dealer_id);
        exit();
    }
    ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>