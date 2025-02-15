<?php
// process_gatepass.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $vehicle_number = $_POST['vehicle_number'];
    $invoice_no = $_POST['invoice_no'];
    $invoice_value = $_POST['invoice_value'];
    $gate_pass_no = $_POST['gate_pass_no'];

    // Database connection
    include 'db_connection.php';

    // Insert data into the database
    $sql = "INSERT INTO gatepass_receipts (customer_name, vehicle_number, invoice_no, invoice_value, gate_pass_no)
            VALUES ('$customer_name', '$vehicle_number', '$invoice_no', $invoice_value, '$gate_pass_no')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record saved successfully.'); window.location.href='gatepass_details.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <title>Gate Pass Receipt</title>
  <style>
    :root {
      --blue-color: #1a4b8c;
      --light-blue: #eef3fc;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --card-bg: white;
      --form-border: #ddd;
      --form-focus: #1a4b8c;
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
      flex: 1;
      padding: 30px;
      margin-left: 250px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: var(--light-blue);
    }

    .form-container {
      width: 100%;
      max-width: 650px;
      background: var(--card-bg);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      border-radius: 15px;
      padding: 40px;
    }

    h1 {
      text-align: center;
      color: var(--blue-color);
      font-size: 28px;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--blue-color);
      display: inline-block;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-size: 16px;
      margin-bottom: 8px;
      color: var(--blue-color);
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid var(--form-border);
      border-radius: 5px;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: var(--form-focus);
      outline: none;
    }

    .btn-container {
      display: flex;
      justify-content: space-between;
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
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo-container">
      <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
    </div>
    <ul class="sidebar-menu">
      <li><a href="../dashboard.html"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
      <li><a href="process_gatepass.php" class="active"><i class="fas fa-clipboard-list"></i><span>Gate Pass Receipt</span></a></li>
      <li><a href="raju.php"><i class="fas fa-users"></i><span>View Details</span></a></li>
    </ul>
  </div>

  <div class="container">
    <div class="form-container">
      <h1>Gate Pass Receipt</h1>
      <form action="process_gatepass.php" method="POST">
        <div class="form-group">
          <label for="customer_name">Customer Name:</label>
          <input type="text" id="customer_name" name="customer_name" required>
        </div>
        <div class="form-group">
          <label for="vehicle_number">Vehicle Number:</label>
          <input type="text" id="vehicle_number" name="vehicle_number" required>
        </div>
        <div class="form-group">
          <label for="invoice_no">Invoice No:</label>
          <input type="text" id="invoice_no" name="invoice_no" required>
        </div>
        <div class="form-group">
          <label for="invoice_value">Invoice Value:</label>
          <input type="number" id="invoice_value" name="invoice_value" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="gate_pass_no">Gate Pass No:</label>
          <input type="text" id="gate_pass_no" name="gate_pass_no" required>
        </div>
        <div class="btn-container">
          <button type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

