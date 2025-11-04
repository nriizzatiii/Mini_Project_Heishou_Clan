<?php
session_start();
include '../components/connect.php';

// Verify admin session
if (!isset($_SESSION['User_ID']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get pending bookings count
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE Status = 'pending'");
 $stmt->execute();
 $result = $stmt->get_result();
 $row = $result->fetch_assoc();
 $pending_bookings = $row['count'];

echo json_encode([
    'success' => true,
    'pending_bookings' => $pending_bookings
]);
?>