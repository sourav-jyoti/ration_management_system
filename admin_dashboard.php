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
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Admin Dashboard - Dealers</h2>
        <div class="grid grid-cols-1 gap-4">
            <?php while ($dealer = $result->fetch_assoc()): ?>
                <a href="admin_dealer.php?dealer_id=<?php echo $dealer['id']; ?>" class="p-4 bg-gray-200 rounded hover:bg-gray-300">
                    <?php echo htmlspecialchars($dealer['username']); ?>
                </a>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <p class="col-span-full text-center text-gray-500">No dealers found.</p>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="mt-6 inline-block bg-red-500 text-white p-2 rounded hover:bg-red-600">Logout</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>