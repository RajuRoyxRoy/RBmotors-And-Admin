<?php 
// Include database connection
include 'db_connection.php';

// Initialize errors array
$errors = [];

// Add Salary
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_salary'])) {
    if (isset($_POST['client_id'], $_POST['per_day_salary'], $_POST['half_day_salary'], $_POST['hourly_salary']) &&
        !empty($_POST['client_id']) && !empty($_POST['per_day_salary']) && !empty($_POST['half_day_salary']) && !empty($_POST['hourly_salary'])) {

        $client_id = $_POST['client_id'];
        $per_day_salary = $_POST['per_day_salary'];
        $half_day_salary = $_POST['half_day_salary'];
        $hourly_salary = $_POST['hourly_salary'];

        $sql = "INSERT INTO client_salary (client_id, per_day_salary, half_day_salary, hourly_salary) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("iddd", $client_id, $per_day_salary, $half_day_salary, $hourly_salary);
            if (!$stmt->execute()) {
                $errors[] = "Failed to add salary: " . $stmt->error;
            }
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    } else {
        $errors[] = "Missing required fields.";
    }

    if (empty($errors)) {
        echo "<script>alert('Salary added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . htmlspecialchars($errors[0]) . "');</script>";
    }
}

// Delete Salary
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM client_salary WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            echo "<script>alert('Salary deleted successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Fetch Employees with Salaries
$sql = "SELECT cs.id, c.id AS client_id, c.first_name, c.middle_name, c.last_name, cs.per_day_salary, cs.half_day_salary, cs.hourly_salary 
        FROM clients c 
        LEFT JOIN client_salary cs ON c.id = cs.client_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #059669;
            --danger-color: #dc2626;
            --background-color: #f0f9ff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
            
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

        .content {
            margin-left: 270px;
            padding: 30px;
            width: 100%;
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

        h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .salary-form {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            transition: transform 0.3s ease;
        }

        .salary-form:hover {
            transform: translateY(-5px);
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--blue-color);
            margin-bottom: 2.5rem;
            text-align: center;
            position: relative;
        }

        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 0.75rem;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .input-field {
            width: 100%;
            padding: 8px 12px; /* Reduced padding */
            font-size: 0.9rem; /* Reduced font size */
            border-radius: 8px;
            border: 1px solid #d1d5db;
            transition: all 0.3s ease;
        }

        .input-field:hover {
            background-color: #fff;
            border-color: #bfdbfe;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.1);
        }

        .input-field:focus {
            border-color: var(--blue-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            outline: none;
            background-color: #fff;
        }

        select.input-field {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232c5aa0'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5em;
            padding-right: 2.5rem;
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
            }

        .submit-button:hover {
                background-color: #0056b3;
                transform: scale(1.03);
            }

        .submit-button:hover::before {
            left: 100%;
        }

        .salary-table {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--blue-color);
            margin-bottom: 2.5rem;
            text-align: center;
            position: relative;
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

        .delete-button {
            background: linear-gradient(145deg, #ef4444, #dc2626);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .delete-button:hover {
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .content {
                padding: 20px;
            }

            .salary-form, .salary-table {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 2rem;
            }
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
            <li><a href="salary.php" class="active"><i class="fas fa-file-invoice"></i><span>Salary Management</span></a></li>
            <li><a href="salaryrecipt.php"><i class="fas fa-file-invoice"></i><span>Salary Record</span></a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="header">
            <h1>Salary Management</h1>
        </div>

        <div class="salary-form">
            <h2 class="form-title">Add New Salary</h2>
            <form action="salary.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="client_id" class="form-label">Select Employee</label>
                        <select name="client_id" id="client_id" class="input-field" required>
                            <option value="">Select Employee</option>
                            <?php
                            $employees = $conn->query("SELECT id, first_name, middle_name, last_name FROM clients");
                            while ($row = $employees->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Per Day Salary</label>
                        <input type="number" name="per_day_salary" class="input-field" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Half Day Salary</label>
                        <input type="number" name="half_day_salary" class="input-field" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Hourly Salary</label>
                        <input type="number" name="hourly_salary" class="input-field" required>
                    </div>

                    <button type="submit" name="add_salary" class="submit-button">
                        Add Salary
                    </button>
                </div>
            </form>
        </div>

        <div class="salary-table">
            <h2 class="table-title">Salary Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Per Day</th>
                        <th>Half Day</th>
                        <th>Hourly</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo "{$row['first_name']} {$row['middle_name']} {$row['last_name']}"; ?></td>
                            <td><?php echo $row['per_day_salary']; ?></td>
                            <td><?php echo $row['half_day_salary']; ?></td>
                            <td><?php echo $row['hourly_salary']; ?></td>
                            <td><a href="?delete_id=<?php echo $row['id']; ?>" class="delete-button">Delete</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
