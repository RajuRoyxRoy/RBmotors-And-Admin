<?php
// Include database connection
include 'db_connection.php';

// Get filter values for salary calculation
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_payment_start_date = isset($_GET['payment_start_date']) ? $_GET['payment_start_date'] : '';
$filter_payment_end_date = isset($_GET['payment_end_date']) ? $_GET['payment_end_date'] : '';

// Build SQL query for salary calculation
$sql = "SELECT 
            c.id AS client_id, 
            c.first_name, 
            c.last_name, 
            cs.per_day_salary, 
            cs.half_day_salary,
            cs.hourly_salary,
            COUNT(CASE WHEN a.attendance_type = 'Present' THEN 1 END) AS present_days,
            COUNT(CASE WHEN a.attendance_type = 'Half-Day' THEN 1 END) AS half_days,
            COUNT(CASE WHEN a.attendance_type = 'Absent' THEN 1 END) AS absent_days
        FROM clients c
        LEFT JOIN attendance a ON c.id = a.client_id
        LEFT JOIN client_salary cs ON c.id = cs.client_id
        WHERE 1"; // Start WHERE condition

if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $sql .= " AND DATE(a.check_in) BETWEEN '$filter_start_date' AND '$filter_end_date'";
}

$sql .= " GROUP BY c.id, c.first_name, c.last_name, cs.per_day_salary, cs.half_day_salary, cs.hourly_salary";

$result = $conn->query($sql);

// Save payment data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_data = $_POST['payment_data'];

    foreach ($payment_data as $client_id => $data) {
        $payment_status = $data['payment_status'];
        $payment_mode = $data['payment_mode'];
        $payment_date = $data['payment_date'];
        $upi_check = $data['upi_check'];

        $query = "INSERT INTO payments (client_id, payment_status, payment_mode, payment_date, upi_check) 
                  VALUES ('$client_id', '$payment_status', '$payment_mode', '$payment_date', '$upi_check')";
        $conn->query($query);
    }

    // Redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all stored payment data with the filter applied
$payment_query = "SELECT p.*, c.first_name, c.last_name FROM payments p
                  LEFT JOIN clients c ON p.client_id = c.id
                  WHERE 1";

if (!empty($filter_payment_start_date) && !empty($filter_payment_end_date)) {
    $payment_query .= " AND DATE(p.payment_date) BETWEEN '$filter_payment_start_date' AND '$filter_payment_end_date'";
}

$payment_result = $conn->query($payment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Calculation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
            display: block;
            margin: 0 auto;
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
            background: #2c5aa0; /* Change background color for active link */
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .container {
            margin-left: 270px;
            width: calc(100% - 280px);
            padding: 20px;
            background: var(--light-blue);
            margin-top: 40px;
            transition: margin-left 0.3s;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .filter-form {
            width: 80%;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .filter-form input,
        .filter-form button {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .filter-form button {
            background-color: #2980b9;
            color: white;
            border: none;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #3498db;
        }

        .salary-table {
            padding: 30px;
            border-radius: 8px;
            
            
        }
        .stored-data {
            padding: 30px; /* Reduce padding */
            border-radius: 8px;
            margin-bottom: 20px; /* Reduce margin-bottom */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
            overflow: hidden;
        }

        table th {
            background: var(--table-header);
            color: white;
            padding: 15px;
            text-align: center;
        }

        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table tr:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .input-field {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.html"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li><a href="attendance_management.php"><i class="fas fa-users"></i><span>Attendance Management</span></a></li>
            <li><a href="attendance_summary.php"><i class="fas fa-file-invoice"></i><span>Attendance Summary</span></a></li>
            <li><a href="leave_management.php"><i class="fas fa-file-invoice"></i><span>Leave Management</span></a></li>
            <li><a href="salary_calculation.php"><i class="fas fa-file-invoice"></i><span>Salary Calculation</span></a></li>
            <li><a href="salary.php"><i class="fas fa-file-invoice"></i><span>Salary Management</span></a></li>
            <li><a href="salaryrecipt.php"  class="active"><i class="fas fa-file-invoice"></i><span>Salary Record</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="header">
            <h1>Payment Record</h1>
        </div>

        <!-- Filter Form for Salary Calculation -->
        <form method="GET" class="filter-form">
            <div class="flex space-x-4">
                <label class="block text-gray-600 font-medium">Start Date:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>" class="input-field">

                <label class="block text-gray-600 font-medium">End Date:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>" class="input-field">

                <button type="submit">Filter</button>
            </div>
        </form>

        <form method="POST">
            <div class="salary-table">
                <table>
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Present Days</th>
                            <th>Half Days</th>
                            <th>Absent Days</th>
                            <th>Base Salary</th>
                            <th>Bonus/ Paid Leave</th>
                            <th>Extra Hours</th>
                            <th>Total Salary</th>
                            <th>Payment Status</th>
                            <th>Payment Mode</th>
                            <th>Payment Date</th>
                            <th>UPI ID / Check No / Cash</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $per_day_salary = (float)$row['per_day_salary'];
                            $half_day_salary = (float)$row['half_day_salary'];
                            $hourly_salary = (float)$row['hourly_salary'];
                            $present_days = (int)$row['present_days'];
                            $half_days = (int)$row['half_days'];

                            // Calculate base salary
                            $base_salary = ($present_days * $per_day_salary) + ($half_days * $half_day_salary);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                <td><?php echo $present_days; ?></td>
                                <td><?php echo $half_days; ?></td>
                                <td><?php echo (int)$row['absent_days']; ?></td>
                                <td><?php echo number_format($base_salary, 2); ?></td>
                                <td>
                                    <input type="number" id="bonus_<?php echo $row['client_id']; ?>" class="input-field" step="0.01" value="0" oninput="calculateTotal(<?php echo $row['client_id']; ?>, <?php echo $base_salary; ?>, <?php echo $hourly_salary; ?>)">
                                </td>
                                <td>
                                    <input type="number" id="extra_hours_<?php echo $row['client_id']; ?>" class="input-field" step="0.01" value="0" oninput="calculateTotal(<?php echo $row['client_id']; ?>, <?php echo $base_salary; ?>, <?php echo $hourly_salary; ?>)">
                                </td>
                                <td class="font-bold" id="total_salary_<?php echo $row['client_id']; ?>">
                                    <?php echo number_format($base_salary, 2); ?>
                                </td>
                                <td>
                                    <select name="payment_data[<?php echo $row['client_id']; ?>][payment_status]" class="input-field">
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="payment_data[<?php echo $row['client_id']; ?>][payment_mode]" class="input-field">
                                        <option value="Bank">Bank</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="date" name="payment_data[<?php echo $row['client_id']; ?>][payment_date]" class="input-field">
                                </td>
                                <td>
                                    <input type="text" name="payment_data[<?php echo $row['client_id']; ?>][upi_check]" class="input-field" placeholder="UPI ID or Check No">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <button type="submit" class="mt-4 py-2 px-6 bg-blue-600 text-white rounded">Save Payment Data</button>
            </div>
        </form>

        <!-- Filter Form for Stored Payment Data -->
        <form method="GET" class="filter-form mt-8">
            <div class="flex space-x-4">
                <label class="block text-gray-600 font-medium">Payment Start Date:</label>
                <input type="date" name="payment_start_date" value="<?php echo htmlspecialchars($filter_payment_start_date); ?>" class="input-field">

                <label class="block text-gray-600 font-medium">Payment End Date:</label>
                <input type="date" name="payment_end_date" value="<?php echo htmlspecialchars($filter_payment_end_date); ?>" class="input-field">

                <button type="submit">Filter Payments</button>
            </div>
        </form>

        <!-- Stored Payment Data -->
        <div class="stored-data mt-8">
            <h2 class="text-xl font-semibold mb-4">Stored Payment Data</h2>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Payment Status</th>
                        <th>Payment Mode</th>
                        <th>Payment Date</th>
                        <th>UPI/Check</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment_row = $payment_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment_row['first_name'] . " " . $payment_row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['payment_mode']); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['upi_check']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function calculateTotal(clientId, baseSalary, hourlySalary) {
            let bonus = parseFloat(document.getElementById(`bonus_${clientId}`).value) || 0;
            let extraHours = parseFloat(document.getElementById(`extra_hours_${clientId}`).value) || 0;
            let totalSalary = baseSalary + (extraHours * hourlySalary) + bonus;
            document.getElementById(`total_salary_${clientId}`).innerText = totalSalary.toFixed(2);
        }
    </script>

</body>
</html>
