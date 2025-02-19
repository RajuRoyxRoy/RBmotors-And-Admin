<?php
// Database Connection
include 'db_connection.php';

// Initialize variables
$filter_account = isset($_GET['bank_account']) ? $_GET['bank_account'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : '';

// Build the query dynamically
$query = "SELECT * FROM transactions WHERE 1=1";

if ($filter_account != '') {
    $query .= " AND bank_account = '$filter_account'";
}

if ($filter_start_date != '' && $filter_end_date != '') {
    $query .= " AND transaction_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
}

if ($filter_type != '') {
    if ($filter_type == 'credit') {
        $query .= " AND credit > 0";
    } elseif ($filter_type == 'debit') {
        $query .= " AND debit > 0";
    }
}

// Execute query
$result = $conn->query($query);

// Fetch bank accounts for the dropdown
$accounts_query = "SELECT * FROM bank_accounts";
$accounts_result = $conn->query($accounts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Log</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            flex-direction: column;
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
            width: calc(100% - 280px);
            padding: 20px;
            background: var(--light-blue);
            margin-top: 40px;
            transition: margin-left 0.3s;
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
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
            padding: 20px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
        }

        label {
            font-size: 1em;
            color: var(--blue-color);
            font-weight: bold;
        }

        input, select {
            padding: 8px 12px;
            border: 1px solid var(--form-border);
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }

        input:focus, select:focus {
            border-color: var(--form-focus);
            outline: none;
            box-shadow: 0 0 6px rgba(26, 75, 140, 0.2);
        }

        input[type="submit"] {
            background-color: var(--blue-color);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #13406c;
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

        table th:first-child, table td:first-child {
            border-left: none;
        }

        table th:last-child, table td:last-child {
            border-right: none;
        }

        .no-transactions {
            text-align: center;
            color: #555;
            font-size: 16px;
            padding: 20px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar-menu span {
                display: none;
            }

            .container {
                margin-left: 80px;
                width: calc(100% - 80px);
            }

            .container {
                margin-left: 80px;
                width: calc(100% - 80px);
            }

            form {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            input[type="submit"] {
                width: 100%;
            }

            table th, table td {
                font-size: 12px;
                padding: 8px;
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
            <li><a href="ledger_management.php"><i class="fas fa-users"></i><span>Add Initial Amount</span></a></li>
            <li><a href="transaction.php" class="active"><i class="fas fa-file-invoice"></i><span>Transaction Log</span></a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Transaction Log</h1>

        <form action="transaction.php" method="GET">
            <div>
                <label for="bank_account">Bank Account:</label>
                <select name="bank_account" id="bank_account">
                    <option value="">All Accounts</option>
                    <?php while ($account = $accounts_result->fetch_assoc()): ?>
                        <option value="<?= $account['account_number'] ?>" 
                            <?= $filter_account == $account['account_number'] ? 'selected' : '' ?>>
                            <?= $account['account_number'] ?> - <?= $account['bank_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?= $filter_start_date ?>">
            </div>

            <div>
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?= $filter_end_date ?>">
            </div>

            <div>
                <label for="transaction_type">Transaction Type:</label>
                <select name="transaction_type" id="transaction_type">
                    <option value="">All Types</option>
                    <option value="credit" <?= $filter_type == 'credit' ? 'selected' : '' ?>>Credit</option>
                    <option value="debit" <?= $filter_type == 'debit' ? 'selected' : '' ?>>Debit</option>
                </select>
            </div>

            <div>
                <button type="submit" name="Filter">Filter</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Bank Account</th>
                    <th>Narration</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Current Balance</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="background-color: <?= $row['narration'] === 'Initial Amount' ? '#ffebcc' : 'inherit' ?>;">
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['bank_account'] ?></td>
                            <td><?= $row['narration'] ?></td>
                            <td style="color: <?= $row['debit'] > 0 ? 'red' : 'inherit' ?>;">
                                <?= $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' ?></td>
                            <td style="color: <?= $row['credit'] > 0 ? 'green' : 'inherit' ?>;">
                                <?= $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' ?></td>
                            <td><?= number_format($row['current_balance'], 2) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($row['transaction_date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-transactions">No transactions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>


