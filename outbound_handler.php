<?php
$servername = "localhost";
$username = "nqftnh";
$password = "Nquangftnh101@";
$dbname = "qlkh";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
    exit;
}

$action = $_POST['action'] ?? '';
$invoicesDir = __DIR__ . '/invoices/';
if (!is_dir($invoicesDir)) {
    mkdir($invoicesDir, 0755, true);
}

if ($action === 'load') {
    $itemsPerPage = 10;
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $offset = ($page - 1) * $itemsPerPage;
    $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

    // Count total items for pagination
    $countSql = "SELECT COUNT(*) FROM outbound_orders";
    $params = [];
    if (!empty($searchTerm)) {
        $countSql .= " WHERE customer_id LIKE :searchTerm OR full_name LIKE :searchTerm OR phone LIKE :searchTerm";
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));

    // Fetch paginated data
    $sql = "SELECT o.*, GROUP_CONCAT(CONCAT(d.product_name, ' (SKU: ', d.sku, ', Số lượng: ', d.quantity_sold, ')') SEPARATOR '<br>') AS details
            FROM outbound_orders o
            LEFT JOIN outbound_order_details d ON o.id = d.order_id";
    if (!empty($searchTerm)) {
        $sql .= " WHERE o.customer_id LIKE :searchTerm OR o.full_name LIKE :searchTerm OR o.phone LIKE :searchTerm";
    }
    $sql .= " GROUP BY o.id ORDER BY o.purchase_date DESC, o.id DESC LIMIT :offset, :itemsPerPage";
    $stmt = $conn->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tableHtml = '';
    if (empty($orders)) {
        $tableHtml = '<tr><td colspan="7" class="text-center">Không tìm thấy đơn hàng</td></tr>';
    } else {
        foreach ($orders as $order) {
            $tableHtml .= "<tr>";
            $tableHtml .= "<td>" . htmlspecialchars($order['phone']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($order['customer_id']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($order['full_name']) . "</td>";
            $tableHtml .= "<td>" . $order['details'] . "</td>";
            $tableHtml .= "<td>" . number_format($order['total_amount'], 0, ',', '.') . "</td>";
            $tableHtml .= "<td>" . date('d/m/Y', strtotime($order['purchase_date'])) . "</td>";
            $tableHtml .= "<td>";
            $tableHtml .= "<button class='btn btn-warning btn-sm edit-btn me-1' data-id='" . $order['id'] . "'>Sửa</button>";
            $tableHtml .= "<button class='btn btn-danger btn-sm delete-btn me-1' data-id='" . $order['id'] . "'>Xóa</button>";
            if (!empty($order['invoice_file'])) {
                $tableHtml .= "<a href='/invoices/" . htmlspecialchars($order['invoice_file']) . "' class='btn btn-info btn-sm' target='_blank'>Hóa Đơn</a>";
            }
            $tableHtml .= "</td>";
            $tableHtml .= "</tr>";
        }
    }

    echo json_encode(['table' => $tableHtml, 'totalPages' => $totalPages]);
    exit;
}

if ($action === 'suggestCustomer') {
    $term = '%' . ($_POST['term'] ?? '') . '%';
    $stmt = $conn->prepare("SELECT customer_id, full_name, phone FROM customers WHERE phone LIKE :term OR customer_id LIKE :term OR full_name LIKE :term LIMIT 5");
    $stmt->bindParam(':term', $term);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
    exit;
}

if ($action === 'suggestProduct') {
    $term = '%' . ($_POST['term'] ?? '') . '%';
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :term LIMIT 5");
    $stmt->bindParam(':term', $term);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
    exit;
}

if ($action === 'getProduct') {
    $sku = $_POST['sku'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM products WHERE sku = :sku");
    $stmt->bindParam(':sku', $sku);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
    exit;
}

if ($action === 'get') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM outbound_orders WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM outbound_order_details WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $id);
    $stmt->execute();
    $order['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($order);
    exit;
}

if ($action === 'add') {
    $customerId = $_POST['customerId'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $purchaseDate = $_POST['purchaseDate'] ?? '';
    $products = $_POST['products'] ?? [];
    $invoiceFile = $_FILES['invoiceFile'] ?? null;
    $invoiceFileName = null;

    // Validate customer
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :customerId AND phone = :phone");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        echo "Mã khách hàng hoặc số điện thoại không hợp lệ!";
        exit;
    }

    // Validate products and calculate total amount
    $totalAmount = 0;
    foreach ($products as $product) {
        $sku = $product['sku'] ?? '';
        $quantitySold = intval($product['quantity']) ?? 0;
        if ($quantitySold <= 0) {
            echo "Số lượng bán phải lớn hơn 0!";
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, sale_price, quantity FROM products WHERE sku = :sku");
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            echo "Sản phẩm với SKU $sku không tồn tại!";
            exit;
        }
        if ($prod['quantity'] < $quantitySold) {
            echo "Số lượng bán của sản phẩm {$prod['name']} vượt quá tồn kho!";
            exit;
        }
        $totalAmount += $quantitySold * $prod['sale_price'];
    }

    try {
        $conn->beginTransaction();

        // Insert order
        $stmt = $conn->prepare("INSERT INTO outbound_orders (customer_id, full_name, phone, total_amount, purchase_date) VALUES (:customerId, :fullName, :phone, :totalAmount, :purchaseDate)");
        $stmt->bindParam(':customerId', $customerId);
        $stmt->bindParam(':fullName', $fullName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':totalAmount', $totalAmount);
        $stmt->bindParam(':purchaseDate', $purchaseDate);
        $stmt->execute();
        $orderId = $conn->lastInsertId();

        // Process invoice file
        if ($invoiceFile && $invoiceFile['size'] > 0) {
            $allowedExtensions = ['pdf', 'png', 'jpg'];
            $ext = strtolower(pathinfo($invoiceFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                throw new Exception("Chỉ chấp nhận file .pdf, .png, .jpg!");
            }
            $formattedDate = str_replace('-', '', $purchaseDate);
            $invoiceFileName = "hoadon_{$phone}_{$formattedDate}_{$orderId}.{$ext}";
            $destination = $invoicesDir . $invoiceFileName;
            if (!move_uploaded_file($invoiceFile['tmp_name'], $destination)) {
                throw new Exception("Lỗi khi tải lên hóa đơn!");
            }
            // Update order with invoice file name
            $stmt = $conn->prepare("UPDATE outbound_orders SET invoice_file = :invoiceFile WHERE id = :orderId");
            $stmt->bindParam(':invoiceFile', $invoiceFileName);
            $stmt->bindParam(':orderId', $orderId);
            $stmt->execute();
        }

        // Insert order details and update inventory
        foreach ($products as $product) {
            $sku = $product['sku'];
            $quantitySold = intval($product['quantity']);
            $stmt = $conn->prepare("SELECT id, name FROM products WHERE sku = :sku");
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("INSERT INTO outbound_order_details (order_id, product_id, product_name, sku, quantity_sold) VALUES (:orderId, :productId, :productName, :sku, :quantitySold)");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':productId', $prod['id']);
            $stmt->bindParam(':productName', $prod['name']);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':quantitySold', $quantitySold);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - :quantitySold WHERE sku = :sku");
            $stmt->bindParam(':quantitySold', $quantitySold);
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
        }

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollBack();
        if ($invoiceFileName && file_exists($invoicesDir . $invoiceFileName)) {
            unlink($invoicesDir . $invoiceFileName);
        }
        echo "Lỗi khi thêm đơn hàng: " . $e->getMessage();
    }
    exit;
}

if ($action === 'update') {
    $orderId = $_POST['orderId'] ?? 0;
    $customerId = $_POST['customerId'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $purchaseDate = $_POST['purchaseDate'] ?? '';
    $products = $_POST['products'] ?? [];
    $invoiceFile = $_FILES['invoiceFile'] ?? null;
    $invoiceFileName = null;

    // Validate customer
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :customerId AND phone = :phone");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        echo "Mã khách hàng hoặc số điện thoại không hợp lệ!";
        exit;
    }

    // Get old order details
    $stmt = $conn->prepare("SELECT invoice_file, phone, purchase_date FROM outbound_orders WHERE id = :orderId");
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    $oldOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldInvoiceFile = $oldOrder['invoice_file'] ?? null;

    // Process new invoice file
    if ($invoiceFile && $invoiceFile['size'] > 0) {
        $allowedExtensions = ['pdf', 'png', 'jpg'];
        $ext = strtolower(pathinfo($invoiceFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            echo "Chỉ chấp nhận file .pdf, .png, .jpg!";
            exit;
        }
        $formattedDate = str_replace('-', '', $purchaseDate);
        $invoiceFileName = "hoadon_{$phone}_{$formattedDate}_{$orderId}.{$ext}";
        $destination = $invoicesDir . $invoiceFileName;
        if (!move_uploaded_file($invoiceFile['tmp_name'], $destination)) {
            echo "Lỗi khi tải lên hóa đơn!";
            exit;
        }
        // Delete old invoice file if exists
        if ($oldInvoiceFile && file_exists($invoicesDir . $oldInvoiceFile)) {
            unlink($invoicesDir . $oldInvoiceFile);
        }
    } else {
        $invoiceFileName = $oldInvoiceFile;
        // If phone or date changed, rename existing file
        if ($oldInvoiceFile && ($phone !== $oldOrder['phone'] || $purchaseDate !== $oldOrder['purchase_date'])) {
            $ext = pathinfo($oldInvoiceFile, PATHINFO_EXTENSION);
            $newFileName = "hoadon_{$phone}_" . str_replace('-', '', $purchaseDate) . "_{$orderId}.{$ext}";
            if (file_exists($invoicesDir . $oldInvoiceFile)) {
                rename($invoicesDir . $oldInvoiceFile, $invoicesDir . $newFileName);
            }
            $invoiceFileName = $newFileName;
        }
    }

    // Get old order details to restore inventory
    $stmt = $conn->prepare("SELECT product_id, quantity_sold FROM outbound_order_details WHERE order_id = :orderId");
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    $oldDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Validate new products and calculate total amount
    $totalAmount = 0;
    foreach ($products as $product) {
        $sku = $product['sku'] ?? '';
        $quantitySold = intval($product['quantity']) ?? 0;
        if ($quantitySold <= 0) {
            echo "Số lượng bán phải lớn hơn 0!";
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, sale_price, quantity FROM products WHERE sku = :sku");
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            echo "Sản phẩm với SKU $sku không tồn tại!";
            exit;
        }

        // Calculate available quantity
        $availableQuantity = $prod['quantity'];
        foreach ($oldDetails as $detail) {
            if ($detail['product_id'] == $prod['id']) {
                $availableQuantity += $detail['quantity_sold'];
            }
        }
        if ($availableQuantity < $quantitySold) {
            echo "Số lượng bán của sản phẩm {$prod['name']} vượt quá tồn kho!";
            exit;
        }
        $totalAmount += $quantitySold * $prod['sale_price'];
    }

    try {
        $conn->beginTransaction();

        // Restore old inventory
        foreach ($oldDetails as $detail) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + :quantitySold WHERE id = :productId");
            $stmt->bindParam(':quantitySold', $detail['quantity_sold']);
            $stmt->bindParam(':productId', $detail['product_id']);
            $stmt->execute();
        }

        // Update order
        $stmt = $conn->prepare("UPDATE outbound_orders SET customer_id = :customerId, full_name = :fullName, phone = :phone, total_amount = :totalAmount, purchase_date = :purchaseDate, invoice_file = :invoiceFile WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->bindParam(':customerId', $customerId);
        $stmt->bindParam(':fullName', $fullName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':totalAmount', $totalAmount);
        $stmt->bindParam(':purchaseDate', $purchaseDate);
        $stmt->bindParam(':invoiceFile', $invoiceFileName);
        $stmt->execute();

        // Delete old order details
        $stmt = $conn->prepare("DELETE FROM outbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        // Insert new order details and update inventory
        foreach ($products as $product) {
            $sku = $product['sku'];
            $quantitySold = intval($product['quantity']);
            $stmt = $conn->prepare("SELECT id, name FROM products WHERE sku = :sku");
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("INSERT INTO outbound_order_details (order_id, product_id, product_name, sku, quantity_sold) VALUES (:orderId, :productId, :productName, :sku, :quantitySold)");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':productId', $prod['id']);
            $stmt->bindParam(':productName', $prod['name']);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':quantitySold', $quantitySold);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - :quantitySold WHERE sku = :sku");
            $stmt->bindParam(':quantitySold', $quantitySold);
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
        }

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollBack();
        if ($invoiceFileName && file_exists($invoicesDir . $invoiceFileName)) {
            unlink($invoicesDir . $invoiceFileName);
        }
        echo "Lỗi khi cập nhật đơn hàng: " . $e->getMessage();
    }
    exit;
}

if ($action === 'delete') {
    $orderId = $_POST['id'] ?? 0;

    try {
        $conn->beginTransaction();

        // Get invoice file
        $stmt = $conn->prepare("SELECT invoice_file FROM outbound_orders WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();
        $invoiceFile = $stmt->fetchColumn();

        // Restore inventory
        $stmt = $conn->prepare("SELECT product_id, quantity_sold FROM outbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + :quantitySold WHERE id = :productId");
            $stmt->bindParam(':quantitySold', $detail['quantity_sold']);
            $stmt->bindParam(':productId', $detail['product_id']);
            $stmt->execute();
        }

        // Delete order details
        $stmt = $conn->prepare("DELETE FROM outbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        // Delete order
        $stmt = $conn->prepare("DELETE FROM outbound_orders WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        // Delete invoice file
        if ($invoiceFile && file_exists($invoicesDir . $invoiceFile)) {
            unlink($invoicesDir . $invoiceFile);
        }

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Lỗi khi xóa đơn hàng: " . $e->getMessage();
    }
    exit;
}
?>