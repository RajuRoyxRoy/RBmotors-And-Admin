<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding products to the cart
if (isset($_POST['add_to_cart'])) {
    $barcode = $_POST['barcode'];
    $quantity = $_POST['quantity'];
    $sale_date = $_POST['sale_date'];

    // Fetch product details
    $stmt = $conn->prepare('SELECT * FROM products WHERE barcode_id = ?');
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        // Check if the requested quantity exceeds available stock
        if ($quantity > $product['total_quantity']) {
            echo "<script>alert('Requested quantity exceeds available stock.');</script>";
        } else {
            $exists = false;

            // Check if product is already in the cart
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['barcode_id'] === $barcode) {
                    // Check if adding this quantity exceeds stock
                    if (($item['quantity'] + $quantity) <= $product['total_quantity']) {
                        $item['quantity'] += $quantity;
                        echo "<script>alert('Product quantity updated in cart.');</script>";
                    } else {
                        echo "<script>alert('Not enough stock available.');</script>";
                    }
                    $exists = true;
                    break;
                }
            }

            // If product is not in the cart, add it
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

// Handle removing products from the cart
if (isset($_POST['remove_item'])) {
    $index = $_POST['index'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
}

// Handle checkout
$total_amount = 0;
if (isset($_POST['checkout'])) {
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['mrp'] * $item['quantity'];
        $total_amount += $item_total;

        // Insert into sales table
        $stmt = $conn->prepare('INSERT INTO sales (product_id, quantity_sold, total_amount, sale_date) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iids', $item['id'], $item['quantity'], $item_total, $item['sale_date']);
        $stmt->execute();

        // Update stock in products table
        $new_stock = $item['total_quantity'] - $item['quantity'];
        $update_stmt = $conn->prepare('UPDATE products SET total_quantity = ? WHERE id = ?');
        $update_stmt->bind_param('ii', $new_stock, $item['id']);
        $update_stmt->execute();
    }

    // Clear the cart after checkout
    $_SESSION['cart'] = [];
    echo "<script>alert('Checkout successful! Total amount: $total_amount');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a4b8c;
            --primary-light: #2c5aa0;
            --secondary-color: #eef3fc;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --success-color: #10b981;
            --danger-color: #ef4444;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            background: var(--secondary-color);
        }
        
        .sidebar {
            width: 250px;
            background: var(--primary-color);
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
        
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-title {
            font-size: 1.875rem;
            color: var(--text-dark);
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 28px;    
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #0ea572;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .actions-container {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
            overflow: hidden;
        }

        .cart-table th {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .cart-total {
            background-color: var(--primary-color);
            color: white;
        }

        .cart-total th {
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script>
        function printCart() {
            var printContents = document.getElementById('cartTable').outerHTML;
            var win = window.open('', '', 'height=500,width=800');
            win.document.write('<html><head><title>Print Cart</title>');
            win.document.write('</head><body >');
            win.document.write(printContents);
            win.document.close();
            win.print();
        }

        // Checkout confirmation
        function confirmCheckout() {
            return confirm("Are you sure you want to checkout?");
        }
    </script>
</head>
<body>
        <div class="sidebar">
            <div class="logo-container">
                <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
            </div>
            <ul class="sidebar-menu">
                <li><a href=../dashboard.html><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
                <li><a href="../attendance/attendance_management.php"><i class="fas fa-clipboard-list"></i><span>Attendance</span></a></li>
                <li><a href="../Employee/employee.html"><i class="fas fa-users"></i><span>Employee</span></a></li>
                <li><a href="solditem.php"><i class="fas fa-chart-line"></i><span>Daily Sales</span></a></li>
                <li><a href="selling.php" class="active"><i class="fas fa-file-invoice"></i><span>Billing</span></a></li>
                <li><a href="index.php"><i class="fas fa-warehouse"></i><span>Warehouse</span></a></li>
                <li><a href="../laser account/ledger_management.php"><i class="fas fa-book"></i><span>Ledger Account</span></a></li>
                <li><a href="../Gatepass/process_gatepass.php"><i class="fas fa-book"></i><span>Gatepass</span></a></li>
                <li><a href="profit_graphs.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
            </ul>
        </div>
    
    <div class="content-wrapper">
        <h1 class="page-title">Sell Products</h1>
        
        <div class="card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="barcode">Barcode</label>
                        <input type="text" name="barcode" id="barcode" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="sale_date">Sale Date</label>
                        <input type="date" name="sale_date" id="sale_date" required>
                    </div>
                    <div class="form-group" style="justify-content: flex-end;">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="actions-container">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="checkout" onclick="return confirmCheckout()" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Checkout
                    </button>
                </form>
                
                <button onclick="printCart()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Cart
                </button>
            </div>
            <h2>Cart Items</h2>

            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Sale Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $index => $item): 
                        $item_total = $item['mrp'] * $item['quantity'];
                        $grand_total += $item_total;
                    ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['mrp']; ?></td>
                            <td><?php echo $item_total; ?></td>
                            <td><?php echo $item['sale_date']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="cart-total">
                        <th colspan="3">Grand Total</th>
                        <th><?php echo $grand_total; ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>
