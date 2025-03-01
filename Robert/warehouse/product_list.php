<?php  
session_start();
include 'db_connection.php'; // Database connection

// Fetch locations and categories
$categories = $conn->query("SELECT * FROM categories");
$locations = $conn->query("SELECT * FROM locations");

// Build query for filtering
$query = "SELECT p.id, p.barcode_id, p.name, p.cost, p.mrp, p.total_quantity, p.date, l.location_name, c.category_name 
          FROM products p 
          JOIN locations l ON p.location_id = l.id
          JOIN categories c ON p.category_id = c.id WHERE 1";

if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
    $query .= " AND p.category_id = " . $_GET['category_id'];
}

if (isset($_GET['start_date']) && $_GET['start_date'] != '' && isset($_GET['end_date']) && $_GET['end_date'] != '') {
    $query .= " AND p.date BETWEEN '" . $_GET['start_date'] . "' AND '" . $_GET['end_date'] . "'";
}

if (isset($_GET['location_id']) && $_GET['location_id'] != '') {
    $query .= " AND p.location_id = " . $_GET['location_id'];
}

if (isset($_GET['barcode_id']) && $_GET['barcode_id'] != '') {
    $query .= " AND p.barcode_id LIKE '%" . $_GET['barcode_id'] . "%'";
}

$result = $conn->query($query);

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding products to the cart
if (isset($_POST['add_to_cart'])) {
    $barcode = $_POST['barcode'];
    $quantity = $_POST['quantity'];
    $sale_date = $_POST['sale_date']; // Added sale date

    // Fetch product details
    $stmt = $conn->prepare('SELECT * FROM products WHERE barcode_id = ?');
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        if ($quantity > $product['total_quantity']) {
            echo "<script>alert('Requested quantity exceeds available stock.');</script>";
        } else {
            $exists = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['barcode_id'] === $barcode) {
                    if (($item['quantity'] + $quantity) <= $product['total_quantity']) {
                        $item['quantity'] += $quantity;
                    } else {
                        echo "<script>alert('Not enough stock available.');</script>";
                    }
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $product['quantity'] = $quantity;
                $product['sale_date'] = $sale_date; // Store sale date
                $_SESSION['cart'][] = $product;
                echo "<script>alert('Product added to cart.');</script>";
            }
        }
    } else {
        echo "<script>alert('Product not found.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
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

        h1 {
            text-align: center;
            color: var(--blue-color);
            font-size: 28px;    
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        h2 {
            text-align: center;
            color: #444;
        }

        /* Sidebar Styles */
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
            margin-left: 280px;
        }

        .form-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
        }

        

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            transition: var(--transition);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        button {
            width: auto;
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

        table tr:nth-child(even) {
            background: #f2f2f2;
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
        <li><a href="index.php"><i class="fas fa-clipboard-list"></i><span>Add Products</span></a></li>
        <li><a href="product_list.php" class="active"><i class="fas fa-users"></i><span>Available Product</span></a></li>
        <li><a href="totalproduct.php"><i class="fas fa-users"></i><span>Total Product</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="form-container">
            <h1>Available Product List</h1>
        
            <form method="GET" action="product_list.php" class="filter-form">
                <h2>Filter Products</h2>
                <select name="category_id">
                    <option value="">Select Category</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= $category['id'] ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= $category['category_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="date" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" placeholder="Start Date">
                <input type="date" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" placeholder="End Date">

                <select name="location_id">
                    <option value="">Select Location</option>
                    <?php while ($location = $locations->fetch_assoc()): ?>
                        <option value="<?= $location['id'] ?>" <?= (isset($_GET['location_id']) && $_GET['location_id'] == $location['id']) ? 'selected' : '' ?>>
                            <?= $location['location_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="barcode_id" value="<?= isset($_GET['barcode_id']) ? $_GET['barcode_id'] : '' ?>" placeholder="Search by Barcode ID">

                <button type="submit">Filter</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Barcode ID</th>
                        <th>Product Name</th>
                        <th>Cost</th>
                        <th>MRP</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $product['barcode_id'] ?></td>
                            <td><?= $product['name'] ?></td>
                            <td><?= $product['cost'] ?></td>
                            <td><?= $product['mrp'] ?></td>
                            <td><?= $product['total_quantity'] ?></td>
                            <td><?= $product['date'] ?></td>
                            <td><?= $product['location_name'] ?></td>
                            <td><?= $product['category_name'] ?></td>
                            <td>
                                <!-- Add to Cart Form -->
                                <form method="POST" action="">
                                    <input type="hidden" name="barcode" value="<?= $product['barcode_id'] ?>">
                                    <input type="number" name="quantity" min="1" max="<?= $product['total_quantity'] ?>" placeholder="Quantity" required>
                                    <input type="date" name="sale_date" required>
                                    <button type="submit" name="add_to_cart">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
