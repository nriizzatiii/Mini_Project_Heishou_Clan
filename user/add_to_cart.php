<?php
header('Content-Type: application/json');
include '../components/connect.php';
include '../components/auth_check.php';

// Require login to access this page
requireLogin();

// Get the raw POST data
 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

// Basic validation
if (!$data || !isset($data['menu_id']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit();
}

 $user_id = $_SESSION['User_ID'];
 $menu_id = $data['menu_id'];
 $quantity = $data['quantity'];

try {
    $menu_stmt = $conn->prepare("SELECT Price FROM menu WHERE Menu_ID = ? AND Status = 'available'");
    $menu_stmt->bind_param("i", $menu_id);
    $menu_stmt->execute();
    $menu_result = $menu_stmt->get_result();

    if ($menu_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Menu item is not available.']);
        exit();
    }
    $menu_item = $menu_result->fetch_assoc();
    $price = $menu_item['Price'];
    $menu_stmt->close();
    $check_stmt = $conn->prepare("SELECT Cart_Item_ID, Quantity FROM cart_item WHERE User_ID = ? AND Menu_ID = ? AND Status = 'active'");
    $check_stmt->bind_param("ii", $user_id, $menu_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $existing_item = $check_result->fetch_assoc();
        $new_quantity = $existing_item['Quantity'] + $quantity;
        $new_subtotal = $new_quantity * $price;

        $update_stmt = $conn->prepare("UPDATE cart_item SET Quantity = ?, Subtotal = ? WHERE Cart_Item_ID = ?");
        $update_stmt->bind_param("idi", $new_quantity, $new_subtotal, $existing_item['Cart_Item_ID']);
        $update_stmt->execute();
        $update_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Item quantity updated in cart.']);

    } else {
        $subtotal = $quantity * $price;
        $insert_stmt = $conn->prepare("INSERT INTO cart_item (User_ID, Menu_ID, Quantity, Subtotal, Status) VALUES (?, ?, ?, ?, 'active')");
        $insert_stmt->bind_param("iiid", $user_id, $menu_id, $quantity, $subtotal);
        $insert_stmt->execute();
        $insert_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
    }

    $check_stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

 $conn->close();
?>