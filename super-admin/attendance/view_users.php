<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['company'] !== 'Super Admin') {
    header("Location: ../index.php");
    exit();
}

// Database credentials
$host = 'localhost';
$db = 'client_data';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Encryption key
$encryption_key = 'secretkey12345'; // Replace with your secure encryption key

// Handle delete user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Check if the user is not Super Admin before allowing deletion
    if ($_SESSION['company'] !== 'Super Admin' || $user_id != 1) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "You cannot delete the Super Admin user.";
    }
}

// Fetch all users
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Users Overview</h2>

        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Username</th>
                    <th>Password Hash</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['company_name']); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['password_hash']); ?></td>
                        <td>
                            <!-- Check if the user is not the Super Admin before displaying delete button -->
                            <?php if ($row['id'] != 1): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn-delete">Delete</button>
                                </form>
                            <?php else: ?>
                                <span>Cannot delete Super Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
