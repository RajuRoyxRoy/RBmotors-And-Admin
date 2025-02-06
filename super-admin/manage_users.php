<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['company'] !== 'Super Admin') {
    header("Location: ../index.php");
    exit();
}

$host = 'localhost';
$db = 'client_data';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $company_name = $_POST['company_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_hash = hash('sha256', $password);

    $stmt = $conn->prepare("INSERT INTO users (company_name, username, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $company_name, $username, $password_hash);
    if ($stmt->execute()) {
        $message = "User added successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle user password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    $password_hash = hash('sha256', $new_password);

    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $password_hash, $user_id);
    if ($stmt->execute()) {
        $message = "Password updated successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all users
$result = $conn->query("SELECT * FROM users");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
    --blue-color: #1a4b8c;
    --light-blue: #eef3fc;
    --shadow-color: rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background: var(--blue-color);
    color: white;
    padding: 20px;
}

.logo-container {
    text-align: center;
    padding: 20px 0;
    margin-bottom: 30px;
    border-bottom: 1px solid white;
}

.logo-container img {
    width: 100px;
    border-radius: 50%;
}

.sidebar-menu {
    list-style: none;
}

.sidebar-menu li {
    margin-bottom: 15px;
}

.sidebar-menu a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 5px;
    transition: 0.3s;
}

.sidebar-menu a:hover {
    background: #2c5aa0;
    box-shadow: 0 4px 8px var(--shadow-color);
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

/* Main Content Styles */
.main-content {
    flex: 1;
    padding: 30px;
    background: #f0f2f5;
}

.section-title {
    color: #1a237e;
    font-size: 28px;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 3px solid #1a237e;
}

.success-message {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #2e7d32;
}

.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    padding: 25px;
    margin-bottom: 30px;
}

.form-title {
    color: #1a237e;
    font-size: 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-title i {
    background: #e8eaf6;
    padding: 8px;
    border-radius: 8px;
    color: #1a237e;
}

.styled-form {
    display: grid;
    gap: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    color: #424242;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #1a237e;
    box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
    outline: none;
}

.btn-submit {
    background: linear-gradient(45deg, #1a237e, #303f9f);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    width: fit-content;
}

.btn-submit:hover {
    background: linear-gradient(45deg, #303f9f, #3949ab);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
}

@media (max-width: 768px) {
    .main-content {
        padding: 20px;
    }
    
    .form-container {
        padding: 20px;
    }
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}



/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
        padding: 10px;
    }

    .sidebar-menu span {
        display: none;
    }

    .cards-container {
        grid-template-columns: 1fr;
    }
}

        </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-container">
                <img src="./" alt="RB Group of Companies">
            </div>
            <ul class="sidebar-menu">
                
                <?php if ($_SESSION['company'] === 'Super Admin'): ?>
                    <li><a href="super-admin/manage_users.php"><i class="fas fa-user-cog"></i><span>Manage Users</span></a></li>
                    <li><a href="super-admin/view_users.php"><i class="fas fa-users"></i><span>View Users</span></a></li>
                <?php endif; ?>
                <li><a href="./attendance/attendance_management.php"><i class="fas fa-car"></i><span>RB Motors</span></a></li>
                <li><a href="./Employee/employee.html"><i class="fas fa-gas-pump"></i><span>RB Filling Station</span></a></li>
                <li><a href="#sales"><i class="fas fa-gas-pump"></i><span>SB Filling Station</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
    <h2 class="section-title">Manage Users</h2>
    <?php if ($message): ?>
        <p class="success-message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h3 class="form-title">Add New User</h3>
        <form method="POST" class="styled-form">
            <div class="form-group">
                <label for="company_name">Select Company</label>
                <select id="company_name" name="company_name" required>
                    <option value="" disabled selected>Select Company</option>
                    <option value="Super Admin">Super Admin</option>
                    <option value="Admin">Admin</option>
                    <option value="RB Motors">RB Motors</option>
                    <option value="RB Station">RB Station</option>
                    <option value="SB Station">SB Station</option>
                </select>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" name="add_user" class="btn-submit">Add User</button>
        </form>
    </div>

    <div class="form-container">
        <h3 class="form-title">Edit User Password</h3>
        <form method="POST" class="styled-form">
            <div class="form-group">
                <label for="user_id">Select User</label>
                <select id="user_id" name="user_id" required>
                    <option value="" disabled selected>Select User</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>"><?= $row['username']; ?> (<?= $row['company_name']; ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input id="new_password" type="password" name="new_password" placeholder="Enter new password" required>
            </div>
            <button type="submit" name="edit_password" class="btn-submit">Update Password</button>
        </form>
    </div>
</div>
    </div>
</body>
</html>