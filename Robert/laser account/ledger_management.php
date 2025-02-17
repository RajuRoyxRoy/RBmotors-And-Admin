<?php
// Database Connection
include 'db_connection.php';

// Add Initial Amount - Check if already added
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initial_amount'])) {
    $initial_amount = $_POST['initial_amount'];

    // Check if initial amount is already added
    $sql_check = "SELECT * FROM transactions WHERE bank_account = 'Initial' LIMIT 1";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $message = "Warning: Initial amount has already been added! You cannot add it again.";
    } else {
        // Add Initial Amount to database
        $sql = "INSERT INTO transactions (bank_account, narration, debit, credit, current_balance) 
                VALUES ('Initial', 'Initial Amount', 0, '$initial_amount', '$initial_amount')";
        if ($conn->query($sql)) {
            $message = "Initial amount added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Process Debit/Credit Transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_amount'])) {
    $bank_account = $_POST['bank_account'];
    $narration = $_POST['narration'];
    $transaction_type = $_POST['transaction_type'];
    $transaction_amount = $_POST['transaction_amount'];

    // Get current balance
    $sql = "SELECT current_balance FROM transactions ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    $current_balance = $result->num_rows > 0 ? $result->fetch_assoc()['current_balance'] : 0;

    if ($transaction_type === 'credit') {
        $current_balance += $transaction_amount;
        $sql = "INSERT INTO transactions (bank_account, narration, debit, credit, current_balance) 
                VALUES ('$bank_account', '$narration', 0, '$transaction_amount', '$current_balance')";
    } elseif ($transaction_type === 'debit') {
        $current_balance -= $transaction_amount;
        $sql = "INSERT INTO transactions (bank_account, narration, debit, credit, current_balance) 
                VALUES ('$bank_account', '$narration', '$transaction_amount', 0, '$current_balance')";
    }

    if ($conn->query($sql)) {
        $message = "Transaction recorded successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Add a New Bank Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_account'])) {
    $account_number = $_POST['new_account'];
    $bank_name = $_POST['bank_name'];

    $sql = "INSERT INTO bank_accounts (account_number, bank_name) VALUES ('$account_number', '$bank_name')";
    if ($conn->query($sql)) {
        $message = "New bank account added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Account Management</title>
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
            background: #2c5aa0; /* Change background color for active link */
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .container {
            margin-left: 270px;
            width: calc(100% - 270px);
            padding: 20px;
            background: var(--light-blue);
            margin-top: 40px;
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
            margin: 25px auto;
            padding: 25px;
            border: 1px solid var(--form-border);
            border-radius: 10px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            
        }

        form div {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--blue-color);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--form-border);
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--form-focus);
            box-shadow: 0 0 6px rgba(26, 75, 140, 0.3);
            outline: none;
        }

        input[type="submit"] {
            width: 15%;
            background-color: var(--blue-color);
            color: white;
            cursor: pointer;
            border: none;
            padding: 8px 16px;
            font-size: 16px;
            border-radius: 50px;
            transition: background 0.3s;
            display: block; /* Make button take up the full width of its container */
            margin: 0 auto; /* Center the button horizontally */
        }

        input[type="submit"]:hover {
            background-color:rgb(12, 110, 239);
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
            margin-top: 30px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px var(--shadow-color);
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: var(--blue-color);
            font-weight: bold;
        }

        .message, .warning {
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
        }

        .message {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .warning {
            color: #d9534f;
            background: rgba(217, 83, 79, 0.1);
        }

        @media (max-width: 768px) {
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
            <li><a href="ledger_management.php" class="active"><i class="fas fa-users"></i><span>Add Initial Amount</span></a></li>
            <li><a href="transaction.php"><i class="fas fa-file-invoice"></i><span>Transaction Log</span></a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Bank Account Management</h1>
        <?php if (isset($message)): ?>
            <p class="<?= isset($message) && strpos($message, 'Warning') !== false ? 'warning' : 'message' ?>"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label for="initial_amount">Add Initial Amount:</label>
                <input type="number" name="initial_amount" id="initial_amount" required>
            </div>
            <button type="submit" name="Add Initial Amount">Add Initial Amount</button>
        </form>

        <form method="POST">
            <div>
                <label for="narration">Narration:</label>
                <textarea name="narration" id="narration" required></textarea>
            </div>
            <div>
                <label for="bank_account">Select Bank Account:</label>
                <select name="bank_account" id="bank_account" required>
                    <?php
                    $sql = "SELECT * FROM bank_accounts";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['account_number']}'>{$row['account_number']} ({$row['bank_name']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="transaction_amount">Transaction Amount:</label>
                <input type="number" name="transaction_amount" id="transaction_amount" required>
            </div>
            <div>
                <label for="transaction_type">Transaction Type:</label>
                <select name="transaction_type" id="transaction_type" required>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
            </div>
            <button type="submit" name="Record Transaction">Record Transaction</button>
        </form>

        <form method="POST">
            <div>
                <label for="new_account">Add New Bank Account:</label>
                <input type="text" name="new_account" id="new_account" placeholder="Account Number" required>
            </div>
            <div>
                <label for="bank_name">Bank Name:</label>
                <input type="text" name="bank_name" id="bank_name" required>
            </div>
            <button type="submit" name="Add Bank Account">Add Bank Account</button>
        </form>
    </div>
</body>
</html>

    </div>
</body>
</html>
