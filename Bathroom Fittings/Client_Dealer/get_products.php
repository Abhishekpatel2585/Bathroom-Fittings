<?php

/**
 * Real-time Product API
 * This file provides product data for the client/dealer dashboard
 * It fetches all products from the database and returns them as JSON
 */

/*header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

session_start();
require_once '../config.php';

// ✅ Check if user is logged in (either admin, client, or dealer)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'products' => []
    ]);
    exit();
}

try {
    // ✅ Fetch all products with their details
    $sql = "SELECT 
                product_id,
                product_name,
                product_count,
                product_price,
                product_active,
                updated_at
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
                'product_active' => $row['product_active'],
                'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s')
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
    ]);
} catch (Exception $e) {
    // ✅ Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage(),
        'products' => []
    ]);
} finally {
    // ✅ Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}*/


/*
 * Real-time Product API
 * Provides product data for the client/dealer dashboard
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

session_start();
require_once '../config.php';

// ------------------------
// TEMP: simulate login for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['is_admin'] = 1;
}
// ------------------------

// Fetch all products
try {
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

    echo json_encode([
        'success' => true,
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        'products' => $products,
        'total_count' => count($products)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage(),
        'products' => []
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
