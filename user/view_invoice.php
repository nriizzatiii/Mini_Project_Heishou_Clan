<?php
session_start();
include '../components/connect.php';
include '../components/auth_check.php';
requireLogin();

// Get Invoice_ID from URL
 $invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($invoice_id <= 0) {
    die("Invalid Invoice ID.");
}

// Get main invoice details
 $stmt = $conn->prepare("SELECT * FROM invoice WHERE Invoice_ID = ?");
 $stmt->bind_param("i", $invoice_id);
 $stmt->execute();
 $invoice_result = $stmt->get_result();
 $invoice = $invoice_result->fetch_assoc();

if (!$invoice) {
    die("Invoice not found.");
}

// Get all items for this invoice
 $stmt_items = $conn->prepare("
    SELECT
        m.Item_Name,
        m.Price,
        ci.Quantity,
        ci.Subtotal
    FROM
        cart_item AS ci
    JOIN
        menu AS m ON ci.Menu_ID = m.Menu_ID
    WHERE
        ci.Invoice_ID = ?
");
 $stmt_items->bind_param("i", $invoice_id);
 $stmt_items->execute();
 $cart_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user details
 $stmt_user = $conn->prepare("SELECT Full_Name FROM user WHERE User_ID = ?");
 $stmt_user->bind_param("i", $invoice['User_ID']);
 $stmt_user->execute();
 $user_result = $stmt_user->get_result();
 $user = $user_result->fetch_assoc();

// Get current user's role to determine redirect
 $current_user_id = $_SESSION['User_ID'];
 $stmt_role = $conn->prepare("SELECT Role FROM user WHERE User_ID = ?");
 $stmt_role->bind_param("i", $current_user_id);
 $stmt_role->execute();
 $role_result = $stmt_role->get_result();
 $current_user = $role_result->fetch_assoc();
 $is_admin = ($current_user['Role'] === 'admin');
 
 // Determine redirect URL based on user role
 $redirect_url = $is_admin ? '../admin/admin_home.php' : 'user_page.php';
 $back_text = $is_admin ? 'Back to Admin Dashboard' : 'Back to Home Page';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo htmlspecialchars($invoice['Invoice_ID']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { 
            font-family: 'Noto Sans JP', sans-serif; 
            background: #1a1a1a; 
            padding: 20px; 
            color: #b0b0b0;
        }
        .invoice-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: #1a1a1a; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
        }
        .invoice-header { 
            text-align: center; 
            border-bottom: 1px solid #333; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .invoice-header h1 { 
            color: #ffffff; 
            margin-bottom: 5px;
        }
        .invoice-header h1 span {
            color: #e53935; 
        }
        .invoice-header p { 
            color: #666; 
        }
        .invoice-details p { 
            margin: 8px 0; 
            color: #999;
        }
        .invoice-details strong {
            color: #aaa;
        }
        h2 {
            color: #888;
            margin-bottom: 15px;
        }
        .invoice-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        .invoice-table th, .invoice-table td { 
            border: none; 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #333;
        }
        .invoice-table th { 
            background-color: #252525; 
            color: #888;
        }
        .invoice-table td {
            color: #999;
        }
        .invoice-table tfoot th {
            color: #aaa;
            border-top: 1px solid #333;
        }
        .text-right { text-align: right; }
        .invoice-footer { 
            text-align: center; 
            margin-top: 40px; 
            color: #666; 
        }
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-primary { 
            background-color: #252525; 
            color: #888;
            border: 1px solid #333;
        }
        .btn-primary:hover {
            background-color: #2a2a2a;
            border-color: #444;
        }
        
        .status-paid {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Heishou <span>Restaurant</span></h1>
            <p>Invoice</p>
        </div>

        <div class="invoice-details">
            <p><strong>Invoice ID:</strong> #<?php echo str_pad($invoice['Invoice_ID'], 6, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['Full_Name']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F d, Y, h:i A', strtotime($invoice['created_at'])); ?></p>
            <p><strong>Payment Status:</strong> <span class="status-<?php echo strtolower(htmlspecialchars($invoice['Payment_Status'])); ?>"><?php echo htmlspecialchars($invoice['Payment_Status']); ?></span></p>
        </div>

        <h2>Order List</h2>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th class="text-right">Total (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['Item_Name']); ?></td>
                    <td><?php echo number_format($item['Price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                    <td class="text-right"><?php echo number_format($item['Subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Grand Total:</th>
                    <th class="text-right"><?php echo htmlspecialchars($invoice['Total_Price']); ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="invoice-footer">
            <p>Thank you for ordering from us!</p>
            <a href="<?php echo $redirect_url; ?>" class="btn btn-primary"><?php echo $back_text; ?></a>
        </div>
    </div>
</body>
</html>