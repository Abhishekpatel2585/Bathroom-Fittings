<?php
session_start();
require_once '../config.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.html");
    exit();
}

$updateMessage = '';

// ✅ Handle updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update product count
    if (isset($_POST['update_count'])) {
        $product_id = $_POST['product_id'];
        $new_count = $_POST['new_count'];

        $stmt = $conn->prepare("UPDATE products SET product_count = ? WHERE product_id = ?");
        $stmt->bind_param("ii", $new_count, $product_id);
        $updateMessage = $stmt->execute()
            ? '<div class="alert alert-success">Product count updated successfully!</div>'
            : '<div class="alert alert-danger">Error updating product count.</div>';
        $stmt->close();
    }

    // Update product price
    elseif (isset($_POST['update_price'])) {
        $product_id = $_POST['product_id'];
        $new_price = $_POST['new_price'];

        $stmt = $conn->prepare("UPDATE products SET product_price = ? WHERE product_id = ?");
        $stmt->bind_param("di", $new_price, $product_id);
        $updateMessage = $stmt->execute()
            ? '<div class="alert alert-success">Product price updated successfully!</div>'
            : '<div class="alert alert-danger">Error updating product price.</div>';
        $stmt->close();
    }

    // Update stock status
    elseif (isset($_POST['set_stock'])) {
        $product_id = $_POST['product_id'];
        $action = $_POST['stock_action'];
        $new_count = ($action === 'out') ? 0 : 1;

        $stmt = $conn->prepare("UPDATE products SET product_count = ? WHERE product_id = ?");
        $stmt->bind_param("ii", $new_count, $product_id);
        $updateMessage = $stmt->execute()
            ? '<div class="alert alert-success">Product stock status updated.</div>'
            : '<div class="alert alert-danger">Error updating stock status.</div>';
        $stmt->close();
    }

    // Toggle active/inactive status
    elseif (isset($_POST['toggle_active'])) {
        $product_id = $_POST['product_id'];
        $new_active = (int)$_POST['active_status'];

        if ($new_active === 0) {
            $stmt = $conn->prepare("UPDATE products SET product_active = ?, product_count = 0 WHERE product_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE products SET product_active = ? WHERE product_id = ?");
        }

        $stmt->bind_param("ii", $new_active, $product_id);
        $updateMessage = $stmt->execute()
            ? '<div class="alert alert-success">Product status updated successfully!</div>'
            : '<div class="alert alert-danger">Error updating product status.</div>';
        $stmt->close();
    }
}

// ✅ Fetch all products
$sql = "SELECT product_id, product_name, product_count, product_price, product_active FROM products ORDER BY product_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bathroom Fittings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Admin-specific styles — special dark minimal design */
        :root {
            --admin-bg: #060708;
            --admin-accent: #ffd700;
            --admin-accent-dark: #c9a300;
            --admin-text: #ffffff;
            --admin-text-muted: rgba(255, 255, 255, 0.65);
            --admin-text-subtle: rgba(255, 255, 255, 0.55);
            --admin-border: rgba(255, 255, 255, 0.04);
            --admin-border-hover: rgba(255, 215, 0, 0.2);
            --transition-smooth: all 0.2s ease-in-out;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(180deg, var(--admin-bg) 0%, #071018 60%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .admin-hero {
            position: fixed;
            inset: 0;
            background: radial-gradient(1000px 400px at 10% 10%, rgba(255, 215, 0, 0.03), transparent 20%);
            pointer-events: none;
            opacity: 0.7;
            animation: heroGlow 8s ease-in-out infinite alternate;
        }

        @keyframes heroGlow {
            0% {
                opacity: 0.5;
            }

            100% {
                opacity: 0.7;
            }
        }

        .dashboard-container {
            min-height: 100vh;
            position: relative;
            z-index: 1;
            padding: 40px 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            animation: fadeInDown 0.6s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-title {
            font-size: 28px;
            margin: 0;
            color: var(--admin-accent);
            font-weight: 800;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 20px rgba(255, 215, 0, 0.15);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--admin-border);
            color: var(--admin-text-muted);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--admin-border-hover);
            color: var(--admin-text);
        }

        .reveal-btn {
            background: linear-gradient(135deg, var(--admin-accent) 0%, var(--admin-accent-dark) 100%);
            color: #111;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
        }

        .reveal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 215, 0, 0.3);
        }

        /* Alert Messages */
        .alert {
            border-radius: 10px;
            margin-bottom: 24px;
            padding: 16px 20px;
            border: 1px solid;
            backdrop-filter: blur(12px);
            animation: slideInDown 0.4s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(74, 222, 128, 0.1);
            border-color: rgba(74, 222, 128, 0.3);
            color: #4ade80;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.3);
            color: #f87171;
        }

        /* Product Table Container */
        .product-table-container {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid var(--admin-border);
            border-radius: 14px;
            padding: 0;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out 0.2s both;
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.6);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Table Styles */
        .product-table {
            width: 100%;
            margin: 0;
        }

        .product-table thead {
            background: rgba(0, 0, 0, 0.3);
        }

        .product-table thead th {
            padding: 18px 16px;
            font-size: 13px;
            font-weight: 700;
            color: var(--admin-accent);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--admin-border);
            white-space: nowrap;
        }

        .product-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: var(--transition-smooth);
            background: rgba(255, 255, 255, 0.01);
        }

        .product-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .product-table tbody tr.inactive {
            opacity: 0.4;
        }

        .product-table tbody td {
            padding: 16px;
            color: var(--admin-text);
            font-size: 14px;
            vertical-align: middle;
        }

        /* Product Name Column */
        .product-name {
            font-weight: 600;
            color: #ffffff;
            font-size: 14px;
        }

        /* Current Count */
        .current-count {
            text-align: center;
            font-weight: 600;
            color: var(--admin-accent);
            font-size: 16px;
        }

        /* Price Column - Centered */
        .price-cell {
            text-align: center;
        }

        .price-form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            max-width: 240px;
            margin: 0 auto;
        }

        .rupee-symbol {
            color: var(--admin-accent);
            font-weight: 700;
            font-size: 16px;
            padding: 0 4px;
        }

        .price-input {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            width: 100px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .price-input:focus {
            outline: none;
            border-color: var(--admin-accent);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.1);
        }

        .price-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Count Input */
        .count-form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            max-width: 180px;
            margin: 0 auto;
        }

        .count-input {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            width: 80px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .count-input:focus {
            outline: none;
            border-color: var(--admin-accent);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.1);
        }

        .count-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Update Buttons */
        .update-btn {
            background: linear-gradient(135deg, var(--admin-accent) 0%, var(--admin-accent-dark) 100%);
            color: #111;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            transition: var(--transition-smooth);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
            white-space: nowrap;
        }

        .update-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }

        .update-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .update-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Stock Status Buttons */
        .stock-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .stock-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
            color: var(--admin-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .stock-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .stock-btn.active {
            background: linear-gradient(135deg, var(--admin-accent) 0%, var(--admin-accent-dark) 100%);
            color: #111;
            border-color: transparent;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
        }

        .stock-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Active Status Button */
        .active-status-btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .active-status-btn.btn-success {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .active-status-btn.btn-success:hover {
            background: rgba(74, 222, 128, 0.25);
            transform: translateY(-1px);
        }

        .active-status-btn.btn-secondary {
            background: rgba(148, 163, 184, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .active-status-btn.btn-secondary:hover {
            background: rgba(148, 163, 184, 0.25);
            transform: translateY(-1px);
        }

        /* Data Obfuscation */
        .data-obfuscate {
            color: transparent;
            text-shadow: 0 0 6px rgba(255, 255, 255, 0.02);
            transition: all 0.18s ease;
            user-select: none;
        }

        input.obfuscate {
            color: transparent !important;
            text-shadow: none !important;
            filter: blur(3px);
        }

        /* Reveal when table has .show-data, or on row hover */
        .product-table-container.show-data .data-obfuscate,
        .product-table-container.show-data input.obfuscate,
        .product-table tbody tr:hover .data-obfuscate,
        .product-table tbody tr:hover input.obfuscate {
            color: inherit !important;
            text-shadow: none !important;
            filter: none !important;
            user-select: text;
        }

        /* Center alignment for specific columns */
        .text-center {
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .product-table {
                font-size: 13px;
            }

            .product-table thead th,
            .product-table tbody td {
                padding: 14px 12px;
            }
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 24px 16px;
            }

            .product-table-container {
                overflow-x: auto;
            }

            .product-table {
                min-width: 1200px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .dashboard-title {
                font-size: 24px;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus visible for keyboard navigation */
        .update-btn:focus-visible,
        .stock-btn:focus-visible,
        .active-status-btn:focus-visible,
        .price-input:focus-visible,
        .count-input:focus-visible {
            outline: 2px solid var(--admin-accent);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <div class="admin-hero"></div>

    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
            <div class="header-actions">
                <a href="logout.php" class="logout-btn">Logout</a>
                <button id="toggleRevealBtn" type="button" class="reveal-btn">Reveal Data</button>
            </div>
        </div>

        <?php if (!empty($updateMessage)) echo "<div id='updateMessageContainer'>$updateMessage</div>"; ?>

        <div class="product-table-container" id="productTableContainer">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Current Count</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Update Count</th>
                        <th class="text-center">Stock Status</th>
                        <th class="text-center">Active Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $pid = $row['product_id'];
                            $pname = htmlspecialchars($row['product_name']);
                            $pcount = (int)$row['product_count'];
                            $pprice = htmlspecialchars($row['product_price']);
                            $active = (int)$row['product_active'];
                            $inStockClass = $pcount > 0 ? 'active' : '';
                            $outStockClass = $pcount == 0 ? 'active' : '';
                            ?>
                            <tr class="<?php echo $active ? '' : 'inactive'; ?>">
                                <td>
                                    <span class="product-name data-obfuscate"><?php echo $pname; ?></span>
                                </td>

                                <td class="text-center">
                                    <span class="current-count data-obfuscate"><?php echo $pcount; ?></span>
                                </td>

                                <!-- Price update -->
                                <td class="price-cell">
                                    <form method="POST" class="price-form">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                        <span class="rupee-symbol">₹</span>
                                        <input type="number" name="new_price" class="price-input obfuscate" step="0.01" min="0" value="<?php echo $pprice; ?>" <?php echo $active ? '' : 'disabled'; ?>>
                                        <button type="submit" name="update_price" class="update-btn" <?php echo $active ? '' : 'disabled'; ?>>Update</button>
                                    </form>
                                </td>

                                <!-- Count update -->
                                <td class="text-center">
                                    <form method="POST" class="count-form">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                        <input type="number" name="new_count" class="count-input obfuscate" min="0" value="<?php echo $pcount; ?>" <?php echo $active ? '' : 'disabled'; ?>>
                                        <button type="submit" name="update_count" class="update-btn" <?php echo $active ? '' : 'disabled'; ?>>Update</button>
                                    </form>
                                </td>

                                <!-- Stock buttons -->
                                <td class="text-center">
                                    <div class="stock-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                            <input type="hidden" name="stock_action" value="in">
                                            <button type="submit" name="set_stock" class="stock-btn <?php echo $inStockClass; ?>" <?php echo $active ? '' : 'disabled'; ?>>In Stock</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                            <input type="hidden" name="stock_action" value="out">
                                            <button type="submit" name="set_stock" class="stock-btn <?php echo $outStockClass; ?>" <?php echo $active ? '' : 'disabled'; ?>>Out of Stock</button>
                                        </form>
                                    </div>
                                </td>

                                <!-- Active toggle -->
                                <td class="text-center">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                        <input type="hidden" name="active_status" value="<?php echo $active ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_active" class="active-status-btn <?php echo $active ? 'btn-success' : 'btn-secondary'; ?>">
                                            <?php echo $active ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 40px;">
                                <span style="color: var(--admin-text-muted);">No products found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Auto-hide success/error message
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.6s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 700);
            }
        }, 4000);

        // Toggle reveal button: adds/removes .show-data on the product-table-container
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('toggleRevealBtn');
            var container = document.getElementById('productTableContainer');
            if (!btn || !container) return;

            btn.addEventListener('click', function() {
                container.classList.toggle('show-data');
                btn.textContent = container.classList.contains('show-data') ? 'Hide Data' : 'Reveal Data';
            });
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>