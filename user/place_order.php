<?php
header('Content-Type: application/json');
include '../components/connect.php';

 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

// Basic validation
if (!$data || !isset($data['User_ID']) || !isset($data['Total_Price']) || !isset($data['cart_items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
    exit();
}

 $user_id = $data['User_ID'];
 $total_price = $data['Total_Price'];
 $cart_items = $data['cart_items'];

// Start a transaction
 $conn->begin_transaction();

try {
    $formatted_total = 'RM ' . number_format($total_price, 2);
    $stmt = $conn->prepare("INSERT INTO invoice (User_ID, Total_Price, Payment_Status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("is", $user_id, $formatted_total);
    $stmt->execute();
    
    // Get the ID of the newly created invoice
    $invoice_id = $conn->insert_id;

    $stmt = $conn->prepare("UPDATE cart_item SET Invoice_ID = ?, Status = 'ordered' WHERE Cart_Item_ID = ? AND User_ID = ?");
    
    foreach ($cart_items as $item) {
        $cart_item_id = $item['Cart_Item_ID'];
        $stmt->bind_param("iii", $invoice_id, $cart_item_id, $user_id);
        $stmt->execute();
    }

    // If everything went well, commit the transaction
    $conn->commit();
    
    // Send success response with the new Invoice_ID
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!',
        'Invoice_ID' => $invoice_id
    ]);

} catch (Exception $e) {
    // If something went wrong, roll back the transaction
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to place order: ' . $e->getMessage()
    ]);
}

 $stmt->close();
 $conn->close();
?>