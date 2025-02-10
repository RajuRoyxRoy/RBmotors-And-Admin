<?php 
// Include database connection
include 'db_connection.php';

// Get filter values
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build SQL query with date filter
$summary_sql = "SELECT 
                    a.client_id, 
                    c.first_name, 
                    c.last_name, 
                    COUNT(CASE WHEN a.attendance_type = 'Present' THEN 1 END) AS present_days,
                    COUNT(CASE WHEN a.attendance_type = 'Absent' THEN 1 END) AS absent_days,
                    COUNT(CASE WHEN a.attendance_type = 'Half-Day' THEN 1 END) AS half_days,
                    COUNT(CASE WHEN a.attendance_type = 'Late Entry' THEN 1 END) AS late_entries
                FROM 
                    attendance a
                JOIN 
                    clients c ON a.client_id = c.id
                WHERE 1"; // Start WHERE condition

if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $summary_sql .= " AND DATE(a.check_in) BETWEEN '$filter_start_date' AND '$filter_end_date'";
}

$summary_sql .= " GROUP BY a.client_id, c.first_name, c.last_name";

$summary_result = $conn->query($summary_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></script>
    <style>
        :root {
            --blue-color: #1a4b8c;
            --light-blue: #eef3fc;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --card-bg: white;
            --form-border: #ddd;
            --form-focus: #1a4b8c;
            --table-header: #007bff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background: var(--light-blue);
        }

        .sidebar {
            width: 250px;
            background: var(--blue-color);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
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

        .sidebar-menu a.active {
            background: #2c5aa0;
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .container {
            margin-left: 270px;
            padding: 2rem;
            max-width: 1400px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .header h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #1a4b8c, #3498db);
            border-radius: 2px;
        }

        .filter-form {
            width: 140%; /* Increase the width to 100% */
            background-color: #D3D3D3;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;        
        }

        .table-container {
            width: 142%; /* Increase the width to 100% */
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .filter-content {
            display: flex;
            gap: 2rem;
            align-items: flex-end;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .date-input-group {
            flex: 1;
            position: relative;
        }

        .date-input-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a4b8c;
            margin-bottom: 0.5rem;
            display: block;
        }

        .date-input-wrapper {
            position: relative;
        }

        .date-input-wrapper input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .date-input-wrapper::before {
            content: '📅';
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #1a4b8c;
            font-size: 1.2rem;
        }

        .date-input-wrapper input:focus {
            border-color: #1a4b8c;
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.1);
            outline: none;
        }

        .btn-filter {
            background: linear-gradient(135deg, #1a4b8c, #2563eb);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }

        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .filter-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-filter {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.html"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li><a href="attendance_management.php"><i class="fas fa-users"></i><span>Attendance Management</span></a></li>
            <li><a href="attendance_summary.php" class="active"><i class="fas fa-file-invoice"></i><span>Attendance Summary</span></a></li>
            <li><a href="leave_management.php"><i class="fas fa-file-invoice"></i><span>Leave Management</span></a></li>
            <li><a href="salary_calculation.php"><i class="fas fa-file-invoice"></i><span>Salary Calculation</span></a></li>
            <li><a href="salary.php"><i class="fas fa-file-invoice"></i><span>Salary Management</span></a></li>
            <li><a href="salaryrecipt.php"><i class="fas fa-file-invoice"></i><span>Salary Record</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="header">
            <h1>Attendance Summary</h1>
        </div>

        <div class="filter-form">
            <h3 class="text-xl font-medium text-gray-800 mb-4">Filter Attendance</h3>
            <form method="GET">
                <div class="filter-content">
                    <div class="date-input-group">
                        <label for="start_date">Start Date</label>
                        <div class="date-input-wrapper">
                            <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                        </div>
                    </div>
                    <div class="date-input-group">
                        <label for="end_date">End Date</label>
                        <div class="date-input-wrapper">
                            <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-filter">Filter</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Present Days</th>
                        <th>Absent Days</th>
                        <th>Half-Days</th>
                        <th>Late Entries</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($summary_result->num_rows > 0): ?>
                        <?php while ($row = $summary_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['client_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['present_days']); ?></td>
                                <td><?php echo htmlspecialchars($row['absent_days']); ?></td>
                                <td><?php echo htmlspecialchars($row['half_days']); ?></td>
                                <td><?php echo htmlspecialchars($row['late_entries']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-6 text-gray-600">No attendance data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
