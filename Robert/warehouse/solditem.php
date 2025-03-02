<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Handle form submission for filtering
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';

// Initialize the SQL query with filtering
$sql = "SELECT sales.id AS sale_id, products.name AS product_name, products.barcode_id, categories.category_name, 
        products.cost, products.mrp, sales.quantity_sold, sales.total_amount, sales.sale_date 
        FROM sales 
        JOIN products ON sales.product_id = products.id 
        JOIN categories ON products.category_id = categories.id
        WHERE 1";

// Apply filters if provided
if ($start_date && $end_date) {
    $sql .= " AND sales.sale_date BETWEEN '$start_date' AND '$end_date'";
}
if ($category_id) {
    $sql .= " AND products.category_id = $category_id";
}

$sql .= " ORDER BY sales.sale_date DESC"; // Optional: order by sale date

$result = $conn->query($sql);

// Handle Export to CSV
if (isset($_POST['export_csv']) && $result && $result->num_rows > 0) {
    // Open the output stream
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=sold_items_report.csv');
    $output = fopen('php://output', 'w');

    // Add CSV column headers
    fputcsv($output, ['Sale ID', 'Product Name', 'Barcode', 'Category', 'Cost Price', 'MRP', 'Quantity Sold', 'Total Amount', 'Profit', 'Sale Date']);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        // Calculate profit
        $profit = $row['total_amount'] - ($row['cost'] * $row['quantity_sold']);
        // Write row to CSV
        fputcsv($output, [
            $row['sale_id'],
            $row['product_name'],
            $row['barcode_id'],
            $row['category_name'],
            number_format($row['cost'], 2),
            number_format($row['mrp'], 2),
            $row['quantity_sold'],
            number_format($row['total_amount'], 2),
            number_format($profit, 2),
            $row['sale_date']
        ]);
    }

    fclose($output);
    exit(); // Stop further processing
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sold Items</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --blue-color: #1a4b8c;
            --light-blue: #eef3fc;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --card-bg: white;
            --form-border: #ddd;
            --form-focus: #1a4b8c;
            --error-color: #e63946;
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
            width: calc(100% - 270px);
            padding: 2rem;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
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
        

        form {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px var(--shadow-color);
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #1a4b8c;
            margin-bottom: 0.5rem;
        }

        select,
        input[type="date"] {
            border: 1px solid var(--form-border);
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            background-color: white;
            transition: all 0.2s;
            width: 100%;
            max-width: 400px;
        }

        select:focus,
        input[type="date"]:focus {
            outline: none;
            border-color: var(--form-focus);
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.2);
        }

        button {
            background-color: var(--blue-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            }

            button:hover {
                background-color: #0056b3;
                transform: scale(1.03);
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

        .export-btn-wrapper {
            text-align: center;
            margin-top: 20px;
        }

        .export-btn-wrapper button {
            background-color: var(--blue-color);
            padding: 12px 25px;
            font-size: 16px;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 6px;
        }

        .export-btn-wrapper button:hover {
            background-color: #0056b3;
            transform: scale(1.03);
        }
    </style>
</head>
<body>
        <div class="sidebar">
            <div class="logo-container">
                <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
            </div>
            <ul class="sidebar-menu">
                <li><a href=../dashboard.html><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
                <li><a href="../attendance/attendance_management.php"><i class="fas fa-clipboard-list"></i><span>Attendance</span></a></li>
                <li><a href="../Employee/addnewclients.php"><i class="fas fa-users"></i><span>Employee</span></a></li>
                <li><a href="solditem.php" class="active"><i class="fas fa-chart-line"></i><span>Daily Sales</span></a></li>
                <li><a href="selling.php"><i class="fas fa-file-invoice"></i><span>Billing</span></a></li>
                <li><a href="index.php"><i class="fas fa-warehouse"></i><span>Warehouse</span></a></li>
                <li><a href="../laser account/ledger_management.php"><i class="fas fa-book"></i><span>Ledger Account</span></a></li>
                <li><a href="../Gatepass/process_gatepass.php"><i class="fas fa-book"></i><span>Gatepass</span></a></li>
                <li><a href="profit_graphs.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
            </ul>
        </div>

    <div class="container">
        <div class="form-container">
            <h1>Sold Items</h1>

            <!-- Filter Form -->
            <form method="POST">
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id">
                        <option value="">Select Category</option>
                        <?php
                        // Fetch categories
                        $categories = $conn->query("SELECT * FROM categories");
                        while ($category = $categories->fetch_assoc()) {
                            echo "<option value='" . $category['id'] . "' " . ($category['id'] == $category_id ? 'selected' : '') . ">" . $category['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>

                <button type="submit">Filter</button>
            </form>

            <!-- Sold Items Table -->
            <table>
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Product Name</th>
                        <th>Barcode</th>
                        <th>Category</th>
                        <th>Cost Price</th>
                        <th>MRP</th>
                        <th>Quantity Sold</th>
                        <th>Total Amount</th>
                        <th>Profit</th>
                        <th>Sale Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Calculate profit
                            $profit = $row['total_amount'] - ($row['cost'] * $row['quantity_sold']);
                            echo "<tr>
                                <td>{$row['sale_id']}</td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['barcode_id']}</td>
                                <td>{$row['category_name']}</td>
                                <td>" . number_format($row['cost'], 2) . "</td>
                                <td>" . number_format($row['mrp'], 2) . "</td>
                                <td>{$row['quantity_sold']}</td>
                                <td>" . number_format($row['total_amount'], 2) . "</td>
                                <td>" . number_format($profit, 2) . "</td>
                                <td>{$row['sale_date']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Export Button -->
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="export-btn-wrapper">
                    <form method="POST">
                        <button type="submit" name="export_csv">Export to CSV</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
