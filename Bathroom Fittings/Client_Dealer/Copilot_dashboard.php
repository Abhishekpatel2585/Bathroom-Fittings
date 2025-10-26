<?php
/**
 * Real-time Product API
 * This file provides product data for the client/dealer dashboard
 * It fetches all products from the database and returns them as JSON
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';

// ✅ Authentication check disabled for testing
// Uncomment the lines below when you want to enable authentication
/*
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'products' => []
    ]);
    exit();
}
*/

try {
    // ✅ Fetch all products with their details
    $sql = "SELECT 
                product_id,
                product_name,
                product_count,
                product_price,
                product_active
            FROM products 
            ORDER BY product_name ASC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    
    $products = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'product_count' => $row['product_count'],
                'product_price' => $row['product_price'],
                'product_active' => $row['product_active']
            ];
        }
    }
    
    // ✅ Return successful response
    echo json_encode([
        'success' => true,
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        'products' => $products,
        'total_count' => count($products)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // ✅ Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage(),
        'products' => []
    ], JSON_PRETTY_PRINT);
} finally {
    // ✅ Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>