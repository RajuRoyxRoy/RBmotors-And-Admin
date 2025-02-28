<?php 
include 'db_connection.php'; // Database connection
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Fetch locations and categories
$locations = $conn->query("SELECT * FROM locations")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Log current locations for debugging
error_log("Current locations: " . print_r($locations, true));

// Add Product
// Add Product
if (isset($_POST['add_product'])) {
    $barcode_id = $_POST['barcode_id'];
    $name = $_POST['name'];
    $cost = $_POST['cost'];
    $mrp = $_POST['mrp'];
    $total_quantity = $_POST['total_quantity'];
    $date = $_POST['date'];
    $location_id = $_POST['location_id'];
    $category_id = $_POST['category_id'];

    // Check if location_id exists
    $location_check = $conn->prepare("SELECT id FROM locations WHERE id = ?");
    $location_check->bind_param('i', $location_id);
    $location_check->execute();
    $location_check->store_result();

    // Check if category_id exists
    $category_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $category_check->bind_param('i', $category_id);
    $category_check->execute();
    $category_check->store_result();

    if ($location_check->num_rows === 0 || $category_check->num_rows === 0) {
        header("Location: index.php?error=Invalid location ID or category ID");
        exit();
    }

    // Insert into products table
    $sql = "INSERT INTO products (barcode_id, name, cost, mrp, total_quantity, date, location_id, category_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssddisii', $barcode_id, $name, $cost, $mrp, $total_quantity, $date, $location_id, $category_id);

    if ($stmt->execute()) {
        header("Location: index.php?success=Product added successfully");
    } else {
        header("Location: index.php?error=Failed to add product: " . $stmt->error);
    }
    exit();
}

// Add Category
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $sql = "INSERT INTO categories (category_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $category_name);

    if ($stmt->execute()) {
        header("Location: index.php?success=Category added successfully");
    } else {
        header("Location: index.php?error=Failed to add category");
    }
    exit();
}

// Delete Category
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $category_id);

    if ($stmt->execute()) {
        header("Location: index.php?success=Category deleted successfully");
    } else {
        header("Location: index.php?error=Failed to delete category");
    }
    exit();
}

// Add Location
if (isset($_POST['add_location'])) {
    $location_name = $_POST['location_name'];
    $sql = "INSERT INTO locations (location_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $location_name);

    if ($stmt->execute()) {
        header("Location: index.php?success=Location added successfully");
    } else {
        header("Location: index.php?error=Failed to add location");
    }
    exit();
}

// Delete Location
if (isset($_POST['delete_location'])) {
    $location_id = $_POST['location_id'];
    $sql = "DELETE FROM locations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $location_id);

    if ($stmt->execute()) {
        header("Location: index.php?success=Location deleted successfully");
    } else {
        header("Location: index.php?error=Failed to delete location");
    }
    exit();
}

// Export Products to Excel
if (isset($_POST['export'])) {
    $category_id = $_POST['export_category_id'];

    $sql = "SELECT p.id, p.barcode_id, p.name, p.cost, p.mrp, p.total_quantity, p.date, l.location_name, c.category_name 
            FROM products p 
            JOIN locations l ON p.location_id = l.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'ID')
        ->setCellValue('B1', 'Barcode ID')
        ->setCellValue('C1', 'Product Name')
        ->setCellValue('D1', 'Cost')
        ->setCellValue('E1', 'MRP')
        ->setCellValue('F1', 'Quantity')
        ->setCellValue('G1', 'Date')
        ->setCellValue('H1', 'Location')
        ->setCellValue('I1', 'Category');

    $row = 2;
    while ($product = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $product['id'])
            ->setCellValue('B' . $row, $product['barcode_id'])
            ->setCellValue('C' . $row, $product['name'])
            ->setCellValue('D' . $row, $product['cost'])
            ->setCellValue('E' . $row, $product['mrp'])
            ->setCellValue('F' . $row, $product['total_quantity'])
            ->setCellValue('G' . $row, $product['date'])
            ->setCellValue('H' . $row, $product['location_name'])
            ->setCellValue('I' . $row, $product['category_name']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'products.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
    exit();
}

// Import Products from Excel
if (isset($_POST['import'])) {
    try {
        // Check if the file is uploaded
        if (!isset($_FILES['file']['tmp_name']) || empty($_FILES['file']['tmp_name'])) {
            throw new Exception("No file uploaded");
        }

        $file = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $data = $spreadsheet->getActiveSheet()->toArray();

        // Log the data being processed
        error_log("Processing data from the Excel file");

        foreach ($data as $index => $row) {
            // Skip header row
            if ($index == 0) continue;

            // Extract and validate data
            $barcode_id = $row[1] ?? null;
            $name = $row[2] ?? null;
            $cost = is_numeric($row[3]) ? (float)$row[3] : null;
            $mrp = is_numeric($row[4]) ? (float)$row[4] : null;
            $total_quantity = is_numeric($row[5]) ? (int)$row[5] : null;
            $date = isset($row[6]) ? date('Y-m-d', strtotime($row[6])) : null;
            $location_name = $row[7] ?? null;
            $category_name = $row[8] ?? null;

            if (!$barcode_id || !$name || !$cost || !$mrp || !$total_quantity || !$date || !$location_name || !$category_name) {
                error_log("Invalid data on row $index. Data: " . print_r($row, true));
                throw new Exception("Invalid data on row $index. Check required fields and data types.");
            }

            // Map location name to location ID
            $stmt = $conn->prepare("SELECT id FROM locations WHERE location_name = ?");
            $stmt->bind_param('s', $location_name);
            $stmt->execute();
            $stmt->bind_result($location_id);
            $stmt->fetch();
            $stmt->close();

            if (!$location_id) {
                error_log("Invalid location name '$location_name' on row $index");
                throw new Exception("Invalid location name on row $index: $location_name");
            }

            // Map category name to category ID
            $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
            $stmt->bind_param('s', $category_name);
            $stmt->execute();
            $stmt->bind_result($category_id);
            $stmt->fetch();
            $stmt->close();

            if (!$category_id) {
                error_log("Invalid category name '$category_name' on row $index");
                throw new Exception("Invalid category name on row $index: $category_name");
            }

            // Insert into products table
            $sql = "INSERT INTO products (barcode_id, name, cost, mrp, total_quantity, date, location_id, category_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssddisii', $barcode_id, $name, $cost, $mrp, $total_quantity, $date, $location_id, $category_id);

            if (!$stmt->execute()) {
                error_log("Error on row $index: " . $stmt->error);
                throw new Exception("Error on row $index: " . $stmt->error);
            }
        }

        header("Location: index.php?success=Products imported successfully");
        exit();
    } catch (Exception $e) {
        error_log("Error during import: " . $e->getMessage());
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Product Management</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --background-color: #f0f9ff;
            --border-color: #e5e7eb;
            --input-focus: #3b82f6;
            --error-color: #ef4444;
            --success-color: #10b981;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --blue-color: #1a4b8c;
            --light-blue: #eef3fc;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --card-bg: white;
            --form-border: #ddd;
            --form-focus: #1a4b8c;
            --error-color: #e63946;
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
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 2rem;
        }

        h1 {
            text-align: center;
            color: var(--blue-color);
            font-size: 28px;    
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .form-section {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .input-group {
            margin-bottom: 1rem;
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s;
            background: #f9fafb;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }

        button {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        button:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        button i {
            font-size: 1rem;
        }

        .tooltip {
            font-size: 0.75rem;
            color: #666;
            margin-top: 0.25rem;
            margin-left: 0.5rem;
        }

        .large-form-section {
            grid-column: 1 / -1;
        }

        .product-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .file-input-container {
            position: relative;
            margin-bottom: 1rem;
        }

        input[type="file"] {
            padding: 1rem;
            border: 2px dashed var(--border-color);
            background: #f8fafc;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: var(--primary-color);
        }

        .download-link {
            display: inline-block;
            margin-top: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .download-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-container {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Keeping the sidebar code unchanged -->
    <div class="sidebar">
        <div class="logo-container">
        <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
        </div>
        <ul class="sidebar-menu">
        <li><a href="../dashboard.html"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
        <li><a href="index.php" class="active"><i class="fas fa-clipboard-list"></i><span>Add Products</span></a></li>
        <li><a href="product_list.php"><i class="fas fa-users"></i><span>Available Product</span></a></li>
        <li><a href="totalproduct.php"><i class="fas fa-users"></i><span>Total Product</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="form-container">
            <h1>Product Management System</h1>
            
            <div class="form-grid">
                <!-- Add Product Form -->
                <div class="form-section large-form-section">
                    <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                    <form method="POST" action="" class="product-form-grid">
                        <div class="input-group">
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="tooltip">Choose a category for the product</div>
                        </div>

                        <div class="input-group">
                            <select name="location_id" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= $location['location_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="tooltip">Choose a storage location</div>
                        </div>

                        <div class="input-group">
                            <input type="text" name="barcode_id" placeholder="Barcode ID" required>
                        </div>

                        <div class="input-group">
                            <input type="text" name="name" placeholder="Product Name" required>
                        </div>

                        <div class="input-group">
                            <input type="number" name="cost" placeholder="Cost" required>
                        </div>

                        <div class="input-group">
                            <input type="number" name="mrp" placeholder="MRP" required>
                        </div>

                        <div class="input-group">
                            <input type="number" name="total_quantity" placeholder="Quantity" required>
                        </div>

                        <div class="input-group">
                            <input type="date" name="date" required>
                        </div>

                        <div class="input-group" style="grid-column: 1 / -1;">
                            <button type="submit" name="add_product">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Location Management -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Location Management</h2>
                    <form method="POST" action="">
                        <div class="input-group">
                            <input type="text" name="location_name" placeholder="New Location Name" required>
                            <button type="submit" name="add_location">
                                <i class="fas fa-plus"></i> Add Location
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="" style="margin-top: 1rem;">
                        <div class="input-group">
                            <select name="location_id" required>
                                <option value="">Select Location to Delete</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= $location['location_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="delete_location">
                                <i class="fas fa-trash"></i> Delete Location
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Category Management -->
                <div class="form-section">
                    <h2><i class="fas fa-tags"></i> Category Management</h2>
                    <form method="POST" action="">
                        <div class="input-group">
                            <input type="text" name="category_name" placeholder="New Category Name" required>
                            <button type="submit" name="add_category">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="" style="margin-top: 1rem;">
                        <div class="input-group">
                            <select name="category_id" required>
                                <option value="">Select Category to Delete</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="delete_category">
                                <i class="fas fa-trash"></i> Delete Category
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Import/Export Section -->
                <div class="form-section">
                    <h2><i class="fas fa-file-export"></i> Import/Export</h2>
                    <form method="POST" action="">
                        <div class="input-group">
                            <select name="export_category_id" required>
                                <option value="">Select Category to Export</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="export">
                                <i class="fas fa-download"></i> Export to Excel
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="" enctype="multipart/form-data" style="margin-top: 1rem;">
                        <div class="file-input-container">
                            <input type="file" name="file" required>
                            <button type="submit" name="import">
                                <i class="fas fa-upload"></i> Import from Excel
                            </button>
                            <a href="./products.xlsx" class="download-link">
                                <i class="fas fa-file-excel"></i> Download excel format
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
