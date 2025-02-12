<?php
// Include database connection
include 'db_connection.php';

// Fetch clients from the database
$sql = "SELECT * FROM clients";
$result = $conn->query($sql);

// Get the current script name to set active status in the sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Clients</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{
            --blue-color : #1a4b8c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            
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
            transition: 0.3s;
        }

        .logo-container {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container img {
            width: 100px;
            aspect-ratio: 1/1;
            border-radius: 50%;
            margin: 0 auto;
        }

        .logo-container .back-button {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            background-color: #2c5aa0;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .logo-container .back-button i {
            font-size: 12px;
        }

        .logo-container .back-button:hover {
            background-color: #1a4b8c;
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
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a.active {
            background: #2c5aa0; /* Change background color for active link */
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background: #E5E5EA;
        }

        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 50px;
            background-color: #f9f9f9;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .main-content .header{
            width: 100%;
            height: fit-content;
            min-height: 10%;
            background-color: white;
            padding: 20px 30px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .main-content .dashboard-text{
            padding: 20px;
        }

        .main-content .dashboard-text h1{
            font-weight: 600;
            letter-spacing: 1px;
        }

        .main-content .dashboard-text p{
            color: grey;
            font-weight: 500;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 25px;
        }

        .card {
            background: white;
            padding: 20px;
            border: 1px solid rgb(180, 180, 180);
            text-align: center;
            cursor: pointer;
            transition: 0.3s ease-in-out;
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .card .icon {
            padding: 5px;
            border-radius: 50%;
            width: 50px;
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .card .icon i {
            font-size: 25px;
            color: black;
        }

        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
        }

        .table th {
            background-color: #4CAF50;
            color: white;
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .table a {
            text-decoration: none;
            font-weight: 600;
        }

        .table a:hover {
            text-decoration: underline;
        }

        .table td a.text-blue-500:hover {
            color: #1d4ed8;
        }

        .table td a.text-red-500:hover {
            color: #ef4444;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .logo-container img {
                width: 50px;
            }

            .sidebar-menu span {
                display: none;
            }

            .sidebar-menu i {
                margin-right: 0;
            }

            .main-content {
                padding: 10px;
            }

            .cards-container {
                grid-template-columns: 1fr;
                padding: 10px;
            }
        }


    </style>
</head>
<body class="bg-gray-200">
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            
                <div class="logo-container">
                    <img src="./image/R.B.png" alt="RB Group of Companies">
                </div>
                <ul class="sidebar-menu">
                    <li><a href="../dashboard.html" id="dashboard-link"><i
                                class="fas fa-tachometer-alt mr-2"></i><span>Dashboard</span></a></li>
                    <li><a href="./addnewclients.php" id="add-client-link"><i
                                class="fas fa-user-plus mr-2"></i><span>Add New Employee</span></a></li>
                    <li><a href="./clients.php"  class="active" id="clients-link"><i
                                class="fas fa-users mr-2"></i><span>Employee</span></a></li>
                    <li><a href="./document.php" id="logout-link"><i
                                class="fas fa-sign-out-alt mr-2"></i><span>Documents</span></a></li>
                </ul>
            
        </div

    <!-- Main content -->
       
            <!-- Main Content -->
<div class="main-content flex-1 p-6 ">
    <div class="main-container mx-auto p-6 bg-white rounded-lg shadow-md">
        <header class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-700">Client List</h1>
            <a href="./addnewclients.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Add New Client
            </a>
        </header>

        <div class="table-container overflow-x-auto">
            <?php if ($result->num_rows > 0): ?>
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead>
                        <tr class="bg-blue-600 text-white uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">ID</th>
                            <th class="py-3 px-6 text-left">First Name</th>
                            <th class="py-3 px-6 text-left">Last Name</th>
                            <th class="py-3 px-6 text-left">Email</th>
                            <th class="py-3 px-6 text-left">Contact</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left"><?php echo $row['id']; ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <a href="edit_client.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:underline">
                                        Edit
                                    </a>
                                    <a href="delete_client.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:underline ml-4">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500 text-center">No clients found.</p>
            <?php endif; ?>

            <?php $conn->close(); ?>
        </div>
    </div>
</div>
        
    </div>
    <script>
        document.getElementById('back-button').addEventListener('click', function() {
            var url = window.location.href;
            var path = url.substring(0, url.lastIndexOf('/'));
            var dashboardUrl = path.substring(0, path.lastIndexOf('/')) + '/dashboard.html';
            window.location.href = dashboardUrl;
            });
        </script>
</body>
</html>