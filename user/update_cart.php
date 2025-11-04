<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../components/connect.php';
include '../components/auth_check.php';


requireLogin();

header('Content-Type: application/json');


function debug_log($message) {
    error_log("[update_cart.php] " . $message);
}

debug_log("Script started");

 $response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['User_ID'])) {
    debug_log("User not logged in");
    $response['message'] = 'User not logged in';
    echo json_encode($response);
    exit();
}

 $action = isset($_POST['action']) ? $_POST['action'] : '';
 $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
 $user_id = $_SESSION['User_ID'];

debug_log("Action: $action, ID: $id, User ID: $user_id");

if ($action === 'remove' && $id > 0) {
    debug_log("Attempting to remove item");
    
    // First check if item exists
    $check_stmt = $conn->prepare(
        "SELECT ci.Cart_Item_ID FROM cart_item ci WHERE ci.Cart_Item_ID = ? AND ci.User_ID = ?"
    );
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        debug_log("Item not found in cart");
        $response['message'] = 'Item not found in your cart';
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Delete the item
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE Cart_Item_ID = ? AND User_ID = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        debug_log("Item deleted successfully");
        
        // Calculate new totals
        $total_stmt = $conn->prepare("SELECT SUM(Quantity) as total_count, SUM(Subtotal) as total_subtotal FROM cart_item WHERE User_ID = ?");
        $total_stmt->bind_param("i", $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $totals = $total_result->fetch_assoc();
        
        $response['success'] = true;
        $response['cart_count'] = $totals['total_count'] ?? 0;
        $response['subtotal'] = $totals['total_subtotal'] ?? 0;
        $response['message'] = 'Item removed successfully';
        
        debug_log("Response prepared: " . json_encode($response));
    } else {
        debug_log("Delete failed: " . $conn->error);
        $response['message'] = 'Failed to remove item: ' . $conn->error;
    }
    $stmt->close();
    $total_stmt->close();
    
} elseif ($action === 'update' && $id > 0) {
    debug_log("Attempting to update item quantity");
    
    $change = isset($_POST['change']) ? (int)$_POST['change'] : 0;
    
    // Get current item details with price from menu table
    $get_stmt = $conn->prepare("SELECT ci.Quantity, ci.Subtotal, ci.Menu_ID, m.Price FROM cart_item ci 
                           JOIN menu m ON ci.Menu_ID = m.Menu_ID 
                           WHERE ci.Cart_Item_ID = ? AND ci.User_ID = ?");
    $get_stmt->bind_param("ii", $id, $user_id);
    $get_stmt->execute();
    $get_result = $get_stmt->get_result();
    
    if ($get_result->num_rows === 0) {
        debug_log("Item not found for update");
        $response['message'] = 'Item not found in your cart';
        echo json_encode($response);
        exit;
    }
    
    $item = $get_result->fetch_assoc();
    $new_quantity = $item['Quantity'] + $change;
    
    debug_log("Current quantity: " . $item['Quantity'] . ", Change: $change, New quantity: $new_quantity");
    
    if ($new_quantity <= 0) {
        // Remove item if quantity is 0 or less
        debug_log("Removing item due to zero quantity");
        $delete_stmt = $conn->prepare("DELETE FROM cart_item WHERE Cart_Item_ID = ? AND User_ID = ?");
        $delete_stmt->bind_param("ii", $id, $user_id);
        
        if ($delete_stmt->execute()) {
            debug_log("Item removed successfully");
        } else {
            debug_log("Failed to remove item: " . $conn->error);
            $response['message'] = 'Failed to remove item: ' . $conn->error;
            echo json_encode($response);
            exit();
        }
        $delete_stmt->close();
    } else {
        // Update quantity
        $new_subtotal = $new_quantity * $item['Price'];
        $update_stmt = $conn->prepare("UPDATE cart_item SET Quantity = ?, Subtotal = ? WHERE Cart_Item_ID = ? AND User_ID = ?");
        $update_stmt->bind_param("idii", $new_quantity, $new_subtotal, $id, $user_id);
        
        if ($update_stmt->execute()) {
            debug_log("Item updated successfully");
            $response['item_subtotal'] = $new_subtotal;
        } else {
            debug_log("Update failed: " . $conn->error);
            $response['message'] = 'Failed to update item: ' . $conn->error;
            echo json_encode($response);
            exit();
        }
        $update_stmt->close();
    }
    
    // Calculate new totals
    $total_stmt = $conn->prepare("SELECT SUM(Quantity) as total_count, SUM(Subtotal) as total_subtotal FROM cart_item WHERE User_ID = ?");
    $total_stmt->bind_param("i", $user_id);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $totals = $total_result->fetch_assoc();
    
    $response['success'] = true;
    $response['cart_count'] = $totals['total_count'] ?? 0;
    $response['subtotal'] = $totals['total_subtotal'] ?? 0;
    $response['new_quantity'] = $new_quantity > 0 ? $new_quantity : null;
    $response['message'] = 'Cart updated successfully';
    
    debug_log("Update response prepared: " . json_encode($response));
    $total_stmt->close();
    
} else {
    debug_log("Invalid action or ID");
    $response['message'] = 'Invalid action or item ID';
}

debug_log("Sending response: " . json_encode($response));
echo json_encode($response);
?>