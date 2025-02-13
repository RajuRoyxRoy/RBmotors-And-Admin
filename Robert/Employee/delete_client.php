<?php
// Include database connection
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];


    // Delete corresponding records from the attendance table
    $sql = "DELETE FROM attendance WHERE client_id = $id";
    $conn->query($sql);

    
    // Delete the client from the database
    $sql = "DELETE FROM clients WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Client deleted successfully!'); window.location.href='clients.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='clients.php';</script>";
    }
} else {
    echo "<script>alert('No client ID provided.'); window.location.href='clients.php';</script>";
}

$conn->close();
?>