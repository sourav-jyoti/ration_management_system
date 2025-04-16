<?php
$conn = new mysqli("localhost", "root", "", "food_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$phone = $_POST['phone'];

$sql = "SELECT email, password FROM users WHERE username = ? AND phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $password = $row['password'];

    // Email configuration
    $to = $email;
    $subject = "Password Recovery - Food Management System";
    $message = "Dear $username,\n\nYour password is: $password\n\nPlease keep this information secure and do not share it with others.\n\nBest regards,\nFood Management Team";
    $headers = "From: no-reply@foodmanagement.com\r\n";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        // Success - Show styled success page
        showResponsePage(
            "Password Recovery Successful",
            "Your password has been sent to your email address.",
            "success",
            "login.html"
        );
    } else {
        // Email sending failed
        showResponsePage(
            "Password Recovery Failed",
            "We couldn't send the email. Please try again later or contact support.",
            "error",
            "login.html"
        );
    }
} else {
    // Invalid credentials
    showResponsePage(
        "Invalid Information",
        "The username or phone number you entered doesn't match our records.",
        "error",
        "login.html"
    );
}

$stmt->close();
$conn->close();

/**
 * Display a styled response page
 * 
 * @param string $title Page title
 * @param string $message Main message to display
 * @param string $type Type of message (success/error)
 * @param string $redirectUrl Where to redirect when button is clicked
 */
function showResponsePage($title, $message, $type, $redirectUrl) {
    $iconClass = ($type == "success") ? "✓" : "✗";
    $colorClass = ($type == "success") ? "#4CAF50" : "#f44336";
    
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $title . ' - Food Management System</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                color: #333;
            }
            .container {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                width: 90%;
                max-width: 500px;
                padding: 40px;
                text-align: center;
                animation: fadeIn 0.5s ease-in-out;
            }
            .icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 80px;
                height: 80px;
                background-color: ' . $colorClass . ';
                color: white;
                font-size: 40px;
                border-radius: 50%;
                margin: 0 auto 20px;
            }
            h1 {
                margin-top: 0;
                color: #333;
                font-size: 24px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .button {
                background-color: #2196F3;
                color: white;
                border: none;
                padding: 12px 30px;
                font-size: 16px;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
                display: inline-block;
            }
            .button:hover {
                background-color: #0b7dda;
            }
            .logo {
                margin-bottom: 30px;
                font-size: 28px;
                font-weight: bold;
                color: #2196F3;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @media (max-width: 600px) {
                .container {
                    width: 85%;
                    padding: 30px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">Food Management System</div>
            <div class="icon">' . $iconClass . '</div>
            <h1>' . $title . '</h1>
            <p>' . $message . '</p>
            <a href="' . $redirectUrl . '" class="button">Return to Login</a>
        </div>
        <script>
            // Auto-redirect after 5 seconds
            setTimeout(function() {
                window.location.href = "' . $redirectUrl . '";
            }, 5000);
        </script>
    </body>
    </html>';
    exit;
}
?>