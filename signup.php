<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Food Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Sign Up</h2>
        <form action="signup_process.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Designation</label>
                <select name="designation" id="designation" class="w-full p-2 border rounded" onchange="toggleDealerDropdown()" required>
                    <option value="">Select Designation</option>
                    <option value="dealer">Dealer</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
            <div id="dealerDropdown" class="mb-4 hidden">
                <label class="block text-gray-700">Select Dealer</label>
                <select name="dealer_id" class="w-full p-2 border rounded">
                    <?php
                    $conn = new mysqli("localhost", "root", "", "food_management");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    $result = $conn->query("SELECT id, username FROM users WHERE designation = 'dealer'");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No dealers available</option>";
                    }
                    $conn->close();
                    ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Sign Up</button>
        </form>
    </div>

    <script>
        function toggleDealerDropdown() {
            const designation = document.getElementById('designation').value;
            const dealerDropdown = document.getElementById('dealerDropdown');
            dealerDropdown.classList.toggle('hidden', designation !== 'customer');
        }
    </script>
</body>
</html>