<?php
header('Content-Type: application/json');
include '../components/connect.php';

session_start();
if (!isset($_SESSION['User_ID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

 $user_id = $_SESSION['User_ID'];
 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

if (isset($data['after_order']) && $data['after_order'] === true) {
    echo json_encode(['success' => true, 'message' => 'Order processed.']);
    exit();
}

 $stmt = $conn->prepare("DELETE FROM cart_item WHERE User_ID = ? AND Status = 'active'");
 $stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear cart.']);
}

 $stmt->close();
 $conn->close();
?>