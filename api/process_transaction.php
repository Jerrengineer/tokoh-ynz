<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi Anda telah habis, silakan login kembali.']);
    exit;
}

// Get raw JSON post data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja kosong!']);
    exit;
}

$items = $data['items'];
$pay_amount = floatval($data['pay_amount']);
$total_price = 0;

// Calculate total and validate stock first
$validated_items = [];
foreach ($items as $item) {
    $product_id = intval($item['id']);
    $qty = intval($item['qty']);
    
    if ($qty <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Jumlah barang harus lebih dari 0!']);
        exit;
    }
    
    // Check product details and stock
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $prod_res = $stmt->get_result();
    
    if ($prod_res->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Produk tidak ditemukan!"]);
        exit;
    }
    
    $product = $prod_res->fetch_assoc();
    $stmt->close();
    
    if ($product['stock'] < $qty) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Stok produk '{$product['name']}' tidak mencukupi! Stok saat ini: {$product['stock']}"]);
        exit;
    }
    
    $item_price = floatval($product['price']);
    $subtotal = $item_price * $qty;
    $total_price += $subtotal;
    
    $validated_items[] = [
        'id' => $product['id'],
        'price' => $item_price,
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

if ($pay_amount < $total_price) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Uang pembayaran kurang dari total belanja!']);
    exit;
}

$change_amount = $pay_amount - $total_price;

// Generate unique invoice number: INV-YYYYMMDD-RAND4
$invoice_no = 'INV-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Insert into transactions table
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO transactions (invoice_no, user_id, total_price, pay_amount, change_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siddd", $invoice_no, $user_id, $total_price, $pay_amount, $change_amount);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan transaksi ke database.");
    }
    
    $transaction_id = $conn->insert_id;
    $stmt->close();
    
    // 2. Insert details and update stocks
    foreach ($validated_items as $v_item) {
        // Insert transaction detail
        $d_stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
        $d_stmt->bind_param("iidid", $transaction_id, $v_item['id'], $v_item['price'], $v_item['qty'], $v_item['subtotal']);
        
        if (!$d_stmt->execute()) {
            throw new Exception("Gagal menyimpan detail transaksi.");
        }
        $d_stmt->close();
        
        // Update product stock
        $s_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $s_stmt->bind_param("ii", $v_item['qty'], $v_item['id']);
        
        if (!$s_stmt->execute()) {
            throw new Exception("Gagal memperbarui stok barang.");
        }
        $s_stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi berhasil diproses!',
        'transaction_id' => $transaction_id,
        'invoice_no' => $invoice_no
    ]);
    
} catch (Exception $e) {
    // Rollback if any query fails
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
?>
