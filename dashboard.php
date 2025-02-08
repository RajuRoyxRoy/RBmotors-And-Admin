<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
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
    background: var(--light-blue);
    padding: 20px;
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

.search {
    display: flex;
    align-items: center;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 25px;
    padding: 5px 15px;
}

.search input {
    border: none;
    outline: none;
    padding: 5px 10px;
    flex-grow: 1;
}

.search button {
    background: var(--blue-color);
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 25px;
    cursor: pointer;
    transition: 0.3s;
}

.search button:hover {
    background: #13406c;
}

.cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px var(--shadow-color);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s;
    display: flex;
    align-items: center;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px var(--shadow-color);
}

.card .icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.card h3 {
    font-size: 1.2em;
    color: #333;
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

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="search">
                    <i class="fa fa-search"></i>
                    <input type="search" name="search" id="search" placeholder="Search...">
                    <button>Search</button>
                </div>
                <div class="profile">
                    <div class="profile-avatar"><i class="fa fa-user-o"></i></div>
                    <div class="name"><?= htmlspecialchars($_SESSION['user']); ?></div>
                </div>
            </div>
            <div class="dashboard-text">
                <h1>Welcome to the Dashboard</h1>
                <p>Track your company's performance</p>
            </div>
            <div class="cards-container">
                <div class="card" onclick="location.href='super-admin/manage_users.php'">
                    <div class="icon" style="background-color: #cfffc0;"><i class="fas fa-user-cog  "></i></div>
                    <h3>Manage Users</h3>
                </div>
                <div class="card" onclick="location.href='super-admin/view_users.php'">
                    <div class="icon" style="background-color: #ACDDDE;"><i class="fas fa-users"></i></div>
                    <h3>View Users</h3>
                </div>
                <div class="card" onclick="location.href=''">
                    <div class="icon" style="background-color: #DFD6F6;"><i class="fas fa-car"></i></div>
                    <h3>RB Motors</h3>
                </div>
                <div class="card" onclick="location.href='#billing'">
                    <div class="icon" style="background-color: #eedd82;"><i class="fas fa-gas-pump"></i></div>
                    <h3>RB Filling Station</h3>
                </div>
                <div class="card" onclick="location.href='./Warehouse management/index.php'">
                    <div class="icon" style="background-color: #82cda8;"><i class="fas fa-gas-pump"></i></div>
                    <h3>SB Filling Station</h3>
                </div>
                <div class="card" onclick="location.href='#reports'">
                    <div class="icon" style="background-color: #f5beff;"><i class="fas fa-chart-bar"></i></div>
                    <h3>Reports</h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>