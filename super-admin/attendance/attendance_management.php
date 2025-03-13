<?php 
// Include database connection
include 'db_connection.php';

// Initialize an errors array
$errors = [];

// Handle Attendance Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_attendance'])) {
    foreach ($_POST['attendance'] as $attendance) {
        // Validate required fields
        if (isset($attendance['client_id'], $attendance['check_in'], $attendance['attendance_type']) &&
            !empty($attendance['client_id']) && !empty($attendance['check_in']) && !empty($attendance['attendance_type'])) {

            $client_id = $attendance['client_id'];
            $check_in = $attendance['check_in'];
            $check_out = $attendance['check_out'] ?? null;
            $attendance_type = $attendance['attendance_type'];

            // Calculate total working hours (if both check-in and check-out are provided)
            $total_hours = null;
            if (!empty($check_in) && !empty($check_out)) {
                $check_in_time = new DateTime($check_in);
                $check_out_time = new DateTime($check_out);
                $interval = $check_in_time->diff($check_out_time);
                $total_hours = $interval->h + ($interval->i / 60);
            }

            // Insert attendance record with error handling
            $sql = "INSERT INTO attendance (client_id, check_in, check_out, total_hours, attendance_type)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("issss", $client_id, $check_in, $check_out, $total_hours, $attendance_type);
                if (!$stmt->execute()) {
                    $errors[] = "Failed to insert attendance for Client ID $client_id: " . $stmt->error;
                }
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
        } else {
            $errors[] = "Missing required fields for one or more employees. Please check and try again.";
        }
    }

    if (empty($errors)) {
        echo "<script>alert('Attendance recorded successfully!');</script>";
    } else {
        // Display an alert for the first error in the list
        echo "<script>alert('Error: " . htmlspecialchars($errors[0]) . "');</script>";
    }
}

// Fetch clients for table
$clients_sql = "SELECT id, first_name, last_name FROM clients";
$clients_result = $conn->query($clients_sql);
if (!$clients_result) {
    die("Error fetching clients: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
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
            background: #2c5aa0; /* Change background color for active link */
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        

        .container {
            margin-left: 270px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .error li {
            margin-bottom: 10px;
        }

        .attendance-table {
            
            padding: 20px 30px;  /* Increased horizontal padding */
            border-radius: 8px;
            
            width: 95%;  /* Increased width */
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
            overflow: hidden;
            table-layout: fixed;  /* Added for consistent column widths */
        }

        table th, table td {
            min-width: 150px;  /* Set minimum width for columns */
            padding: 15px 25px;  /* Increased horizontal padding */
        }
        table th {
            background: var(--table-header);
            color: white;
            text-align: center;
            padding: 15px;
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

        .submit-button {
            background-color: var(--blue-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-top: 10px;
            }

            .submit-button:hover {
                background-color: #0056b3;
                transform: scale(1.03);
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
            <li><a href="attendance_management.php"class="active"><i class="fas fa-users"></i><span>Attendance Management</span></a></li>
            <li><a href="attendance_summary.php"><i class="fas fa-file-invoice"></i><span>Attendance Summary</span></a></li>
            <li><a href="leave_management.php"><i class="fas fa-file-invoice"></i><span>Leave Management</span></a></li>
            <li><a href="salary_calculation.php"><i class="fas fa-file-invoice"></i><span>Salary Calculation</span></a></li>
            <li><a href="salary.php"><i class="fas fa-file-invoice"></i><span>Salary Management</span></a></li>
            <li><a href="salaryrecipt.php"><i class="fas fa-file-invoice"></i><span>Salary Record</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="header">
            <h1>Employee Attendance Management</h1>
        </div>

        <!-- Error Display -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Attendance Management Table -->
        <div class="attendance-table">
            <form action="attendance_management.php" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Attendance Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $clients_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="attendance[<?php echo $row['id']; ?>][client_id]" value="<?php echo $row['id']; ?>">
                                    <?php echo $row['id']; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td>
                                    <input type="datetime-local" name="attendance[<?php echo $row['id']; ?>][check_in]" class="input-field">
                                </td>
                                <td>
                                    <input type="datetime-local" name="attendance[<?php echo $row['id']; ?>][check_out]" class="input-field">
                                </td>
                                <td>
                                    <select name="attendance[<?php echo $row['id']; ?>][attendance_type]" class="input-field">
                                        <option value="">Select</option>
                                        <option value="Present">Present</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Half-Day">Half-Day</option>
                                        <option value="Late Entry">Late Entry</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="submit_attendance" class="submit-button">Submit Attendance</button>
            </form>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>
