<?php
// Include database connection
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM clients WHERE id = $id";
    $result = $conn->query($sql);
    $client = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Fetch form data
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // Update data in the database
    $sql = "UPDATE clients SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name', gender='$gender', dob=STR_TO_DATE('$dob', '%d/%m/%Y'), email='$email', contact='$contact', address='$address' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Client updated successfully!'); window.location.href='clients.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Edit Client</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="employee.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
</head>
<body class="bg-gray-200">
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-container">
                <img src="./image/R.B.png" alt="RB Group of Companies">
            </div>
            <ul class="sidebar-menu">
                <li><a href="./employee.html" id="dashboard-link">
                    <i class="fas fa-tachometer-alt mr-2"></i><span>Dashboard</span>
                </a></li>
                <li><a href="./addnewclients.php" id="add-client-link">
                    <i class="fas fa-user-plus mr-2"></i><span>Add New Employee</span>
                </a></li>
                <li><a href="./clients.php" id="clients-link">
                    <i class="fas fa-users mr-2"></i><span>Employee</span>
                </a></li>
                <li><a href="./document.php" id="logout-link">
                    <i class="fas fa-sign-out-alt mr-2"></i><span>Documents</span>
                </a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-1 p-6">
            <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Employee</h2>
                <form action="edit_client.php?id=<?php echo $client['id']; ?>" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($client['first_name']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="<?php echo htmlspecialchars($client['middle_name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($client['last_name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <input type="text" name="gender" value="<?php echo htmlspecialchars($client['gender']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="text" name="dob" id="dob" value="<?php echo date('d/m/Y', strtotime($client['dob'])); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Contact -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                            <input type="text" name="contact" value="<?php echo htmlspecialchars($client['contact']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <!-- Address (full width) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($client['address']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="clients.php" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" name="update" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Update Employee</button>
                    </div>
                </form>
                <button onclick="window.history.back()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#dob", {
                dateFormat: "d/m/Y",
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>