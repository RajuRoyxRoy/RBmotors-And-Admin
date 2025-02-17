<?php
// gatepass_details.php

// Database connection
include 'db_connection.php';

// Initialize variables for print
$printData = null;
$filters = [];

// Check if the user submitted the form to filter the records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Capture filter inputs if available
    $vehicle_number = $_GET['vehicle_number'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $invoice_no = $_GET['invoice_no'] ?? '';

    // Prepare filter query
    $filter_query = "SELECT * FROM gatepass_receipts WHERE 1";

    if ($vehicle_number) {
        $filter_query .= " AND vehicle_number LIKE '%$vehicle_number%'";
    }
    if ($start_date && $end_date) {
        $filter_query .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
    }
    if ($invoice_no) {
        $filter_query .= " AND invoice_no LIKE '%$invoice_no%'";
    }

    // Execute the query
    $result = $conn->query($filter_query);
}

// Check if the user submitted the form to print details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gate_pass_no'], $_POST['vehicle_number'])) {
    $gate_pass_no = $_POST['gate_pass_no'];
    $vehicle_number = $_POST['vehicle_number'];

    // Query to fetch the specific record
    $sql = "SELECT * FROM gatepass_receipts WHERE gate_pass_no = ? AND vehicle_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $gate_pass_no, $vehicle_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $printData = $result->fetch_assoc();
    } else {
        echo "<script>alert('No record found for the entered Gate Pass Number and Vehicle Number.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gate Pass Details</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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

    h1 {
      text-align: center;
      color: #333;
      font-size: 28px;
      margin-bottom: 30px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--blue-color);
      display: inline-block;
    }

    .content {
      margin-left: 270px;
      padding: 30px;
      width: calc(100% - 270px);
    }

    .form-container {
      margin: 20px 0;
      background: var(--card-bg);
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 8px var(--shadow-color);
    }

    .form-container form {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
      justify-content: space-between;
    }

    .form-container input {
      padding: 12px;
      font-size: 16px;
      flex: 1;
      min-width: 200px;
      border-radius: 6px;
      border: 1px solid var(--form-border);
      transition: 0.3s;
    }

    .form-container input:focus {
      border-color: var(--form-focus);
      box-shadow: 0 0 5px var(--form-focus);
    }

    .form-container button {
      background-color: var(--blue-color);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.3s;
    }

    .form-container button:hover {
      background-color: #164073;
      transform: scale(1.03);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      background: var(--card-bg);
      box-shadow: 0 2px 5px var(--shadow-color);
      border-radius: 8px;
      overflow: hidden;
    }

    table th, table td {
      padding: 12px;
      text-align: left;
      border: 1px solid #ddd;
      font-size: 16px;
    }

    table th {
      background-color: var(--blue-color);
      color: white;
    }

    table tr:nth-child(even) {
      background: #f9f9f9;
    }

    .print-button {
      background-color: #28a745;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      font-size: 14px;
      transition: 0.3s;
    }

    .print-button:hover {
      background-color: #218838;
    }

    .print-container {
      margin-top: 30px;
      background-color: var(--blue-color);
      color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px var(--shadow-color);
    }

    .details p {
      margin: 12px 0;
      font-size: 18px;
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
    <li><a href="process_gatepass.php"><i class="fas fa-clipboard-list"></i><span>Gate Pass Receipt</span></a></li>
    <li><a href="raju.php" class="active"><i class="fas fa-users"></i><span>View Details</span></a></li>
  </ul>
</div>

<div class="content">
  <h1>Gate Pass Details</h1>

  <!-- Filter Form -->
  <div class="form-container">
    <form method="GET" action="">
      <input type="text" name="vehicle_number" placeholder="Vehicle Number" value="<?= $_GET['vehicle_number'] ?? '' ?>">
      <input type="date" name="start_date" placeholder="Start Date" value="<?= $_GET['start_date'] ?? '' ?>">
      <input type="date" name="end_date" placeholder="End Date" value="<?= $_GET['end_date'] ?? '' ?>">
      <input type="text" name="invoice_no" placeholder="Invoice Number" value="<?= $_GET['invoice_no'] ?? '' ?>">
      <button type="submit">Filter</button>
    </form>
  </div>

  <!-- Display Filtered Records -->
  <table>
    <thead>
      <tr>
        <th>Customer Name</th>
        <th>Vehicle Number</th>
        <th>Invoice No</th>
        <th>Invoice Value</th>
        <th>Gate Pass No</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                  <td>{$row['customer_name']}</td>
                  <td>{$row['vehicle_number']}</td>
                  <td>{$row['invoice_no']}</td>
                  <td>{$row['invoice_value']}</td>
                  <td>{$row['gate_pass_no']}</td>
                  <td>{$row['created_at']}</td>
                  <td>
                    <form method='POST' action=''>
                      <input type='hidden' name='gate_pass_no' value='{$row['gate_pass_no']}'>
                      <input type='hidden' name='vehicle_number' value='{$row['vehicle_number']}'>
                      <button type='submit' class='print-button'>Print</button>
                    </form>
                  </td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='7'>No records found</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <!-- Print Details Section -->
  <?php if ($printData): ?>
    <div class="print-container">
      <h2>Gate Pass Details</h2>
      <div class="details">
        <p><strong>Customer Name:</strong> <?= $printData['customer_name']; ?></p>
        <p><strong>Vehicle Number:</strong> <?= $printData['vehicle_number']; ?></p>
        <p><strong>Invoice No:</strong> <?= $printData['invoice_no']; ?></p>
        <p><strong>Invoice Value:</strong> ₹<?= $printData['invoice_value']; ?></p>
        <p><strong>Gate Pass No:</strong> <?= $printData['gate_pass_no']; ?></p>
        <p><strong>Date:</strong> <?= $printData['created_at']; ?></p>
      </div>
      <button onclick="printDetails()">Print Details</button>
    </div>
  <?php endif; ?>
</div>

<script>
  function printDetails() {
    var printWindow = window.open('', '', 'height=600,width=800');
    var printContent = `
      <html>
        <head>
          <title>Gate Pass Details</title>
          <style>
            body { font-family: Arial, sans-serif; color: #333; }
            h2 { text-align: center; margin-top: 20px; }
            .details { padding: 25px; border: 1px solid #ddd; margin-top: 30px; }
            .details p { margin: 12px 0; font-size: 18px; }
          </style>
        </head>
        <body>
          <h2>Gate Pass Details</h2>
          <div class="details">
            <p><strong>Customer Name:</strong> ${'<?= $printData['customer_name']; ?>'}</p>
            <p><strong>Vehicle Number:</strong> ${'<?= $printData['vehicle_number']; ?>'}</p>
            <p><strong>Invoice No:</strong> ${'<?= $printData['invoice_no']; ?>'}</p>
            <p><strong>Invoice Value:</strong> ₹${'<?= $printData['invoice_value']; ?>'}</p>
            <p><strong>Gate Pass No:</strong> ${'<?= $printData['gate_pass_no']; ?>'}</p>
            <p><strong>Date:</strong> ${'<?= $printData['created_at']; ?>'}</p>
          </div>
        </body>
      </html>
    `;
    printWindow.document.open();
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
  }
</script>
</body>
</html>

