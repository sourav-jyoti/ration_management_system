<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Food Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .form-card {
            transition: all 0.3s ease;
        }
        
        .form-card:hover {
            transform: translateY(-5px);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .input-with-icon {
            padding-left: 35px !important;
        }
        
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #4c6ef5 0%, #3b5bdb 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #3b5bdb 0%, #364fc7 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 91, 219, 0.4);
        }
        
        .shine-effect {
            position: relative;
            overflow: hidden;
        }
        
        .shine-effect::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% {
                left: -100%;
                opacity: 0;
            }
            10% {
                left: -100%;
                opacity: 0.5;
            }
            20% {
                left: 100%;
                opacity: 0;
            }
            100% {
                left: 100%;
                opacity: 0;
            }
        }
        
        .progress-container {
            height: 5px;
            width: 100%;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-top: 5px;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-weak {
            background-color: #dc3545;
        }
        
        .progress-medium {
            background-color: #ffc107;
        }
        
        .progress-strong {
            background-color: #28a745;
        }
        
        .status-text {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .status-weak {
            color: #dc3545;
        }
        
        .status-medium {
            color: #ffc107;
        }
        
        .status-strong {
            color: #28a745;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .step {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #e9ecef;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background-color: #3b5bdb;
            transform: scale(1.2);
        }
        
        /* Custom select styling */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 10px) center;
            padding-right: 30px !important;
        }
        
        /* Fade-in animation for dealer dropdown */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
        .alert-box {
        display: none;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen py-10">
    

    <div class="w-full max-w-md px-4">
        <!-- Alert box -->
    <div id="alertBox" class="alert-box bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <strong class="font-bold">Oops!</strong>
      <span class="block sm:inline" id="alertMessage">Password too short.</span>
      <span onclick="hideAlert()" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
          <title>Close</title>
          <path d="M14.348 5.652a1 1 0 0 0-1.414 0L10 8.586 7.066 5.652a1 1 0 1 0-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 1 0 1.414 1.414L10 11.414l2.934 2.934a1 1 0 0 0 1.414-1.414L11.414 10l2.934-2.934a1 1 0 0 0 0-1.414z"/>
        </svg>
      </span>
    </div>
        <div class="form-card bg-white p-8 rounded-lg shadow-xl mb-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-blue-500"></div>
            
            <div class="flex justify-center mb-4">
                <div class="h-16 w-16 rounded-full bg-blue-50 flex items-center justify-center shine-effect">
                    <i class="fas fa-user-plus text-blue-500 text-2xl"></i>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold mb-2 text-center text-gray-800">Join Our Community</h2>
            <p class="text-center text-gray-600 mb-6 text-sm">Create an account to get started</p>
            
            <form action="signup_process.php" method="POST" onsubmit = "return validateForm()">
                <div class="mb-4 input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input 
                        type="text" 
                        name="username" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200" 
                        placeholder="Choose a username"
                        required
                    >
                </div>
                
                <div class="mb-1 input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200" 
                        placeholder="Create a password"
                        onkeyup="checkPasswordStrength(this.value)"
                        required
                    >
                    <i class="far fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
                
                <div class="mb-4">
                    <div class="progress-container">
                        <div id="password-strength" class="progress-bar"></div>
                    </div>
                    <p id="password-status" class="status-text"></p>
                </div>
                
                <div class="mb-4 input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input 
                        type="email" 
                        name="email" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200" 
                        placeholder="Email address"
                        required
                    >
                </div>
                
                <div class="mb-4 input-group">
                    <i class="fas fa-phone input-icon"></i>
                    <input 
                        type="text" 
                        name="phone" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200" 
                        placeholder="Phone number"
                        required
                    >
                </div>
                
                <div class="mb-4 input-group">
                    <i class="fas fa-id-badge input-icon"></i>
                    <select 
                        name="designation" 
                        id="designation" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200" 
                        onchange="toggleDealerDropdown()" 
                        required
                    >
                        <option value="">Select your role</option>
                        <option value="dealer">Dealer</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div id="dealerDropdown" class="mb-6 hidden">
                    <div class="input-group">
                        <i class="fas fa-store input-icon"></i>
                        <select 
                            name="dealer_id" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-with-icon transition duration-200"
                        >
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
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i> Select the dealer you would like to work with
                    </p>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full btn-gradient text-white p-3 rounded-lg font-medium transition duration-200"
                >
                    Create Account
                </button>
            </form>
            
            <p class="mt-6 text-center text-gray-600">
                Already have an account? 
                <a 
                    href="login.html" 
                    class="text-blue-500 hover:text-blue-700 font-medium transition duration-200"
                >
                    Sign In
                </a>
            </p>
        </div>
        
        <p class="text-center text-gray-500 text-sm">© 2025 Food Management System</p>
    </div>

    <script>
        function toggleDealerDropdown() {
            const designation = document.getElementById('designation').value;
            const dealerDropdown = document.getElementById('dealerDropdown');
            
            if (designation === 'customer') {
                dealerDropdown.classList.remove('hidden');
                dealerDropdown.classList.add('fade-in');
            } else {
                dealerDropdown.classList.add('hidden');
            }
        }
        
        function togglePassword() {
            const password = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (password.type === 'password') {
                password.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        function checkPasswordStrength(password) {
            const progressBar = document.getElementById('password-strength');
            const statusText = document.getElementById('password-status');
            
            // Remove existing classes
            progressBar.classList.remove('progress-weak', 'progress-medium', 'progress-strong');
            statusText.classList.remove('status-weak', 'status-medium', 'status-strong');
            
            if (password.length === 0) {
                progressBar.style.width = '0%';
                statusText.textContent = '';
                return;
            }
            
            // Simple password strength check
            let strength = 0;
            
            // Length check
            if (password.length > 6) strength += 1;
            if (password.length > 10) strength += 1;
            
            // Character variety check
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update UI based on strength
            if (strength <= 2) {
                progressBar.style.width = '33%';
                progressBar.classList.add('progress-weak');
                statusText.textContent = 'Weak password';
                statusText.classList.add('status-weak');
            } else if (strength <= 4) {
                progressBar.style.width = '66%';
                progressBar.classList.add('progress-medium');
                statusText.textContent = 'Medium password';
                statusText.classList.add('status-medium');
            } else {
                progressBar.style.width = '100%';
                progressBar.classList.add('progress-strong');
                statusText.textContent = 'Strong password';
                statusText.classList.add('status-strong');
            }
        }


        function validateForm() {
            const pwd = document.getElementById('password').value;
            if (pwd.length < 4) {
                showAlert("Password must be at least 4 characters.");
                 return false;
            }
                return true;
            }
        function showAlert(message) {
            document.getElementById("alertMessage").innerText = message;
            document.getElementById("alertBox").style.display = "block";
        }

        function hideAlert() {
            document.getElementById("alertBox").style.display = "none";
        }
    </script>
</body>
</html>"