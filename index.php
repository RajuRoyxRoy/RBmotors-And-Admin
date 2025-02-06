<?php
session_start();
$host = 'localhost';
$db = 'client_data';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = $_POST['company'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check credentials
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE company_name = ? AND username = ?");
    $stmt->bind_param("ss", $company, $username);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    if ($stmt->fetch() && hash('sha256', $password) === $password_hash) {
        $_SESSION['user'] = $username;
        $_SESSION['company'] = $company;
        if ($company === 'Super Admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: Rb-Motor/dashboard.html");
        }
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Company Login</title>
    
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="image/R.B.png" alt="RB Logo" class="logo">
            </div>
            <h2>LOGIN TO YOUR ACCOUNT</h2>
            <form id="loginForm" action="" method="POST">
                <div class="form-group">
                    <label for="company">Select Company:</label>
                    <select name="company" id="company" required onchange="toggleForgotPassword()">
                        <option value="" disabled selected>Select your company</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Admin">Admin</option>
                        <option value="RB Motors">RB Motors</option>
                        <option value="RB Station">RB Station</option>
                        <option value="SB Station">SB Station</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="username">User  Name</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-button">ENTER</button>
            </form>
            <?php if ($error): ?>
                <div id="message" style="color: red;"><?= $error; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>