<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "(p.code LIKE '%$search%' OR p.name LIKE '%$search%')";
}

if ($category_id > 0) {
    $where_clauses[] = "p.category_id = $category_id";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$query = "SELECT p.id, p.code, p.name, p.price, p.stock, p.min_stock, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where_sql 
          ORDER BY p.name ASC";

$result = $conn->query($query);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Format price and stock warnings
        $row['price_formatted'] = format_rupiah($row['price']);
        $row['is_low_stock'] = ($row['stock'] <= $row['min_stock']);
        $products[] = $row;
    }
}

echo json_encode($products);
?>
