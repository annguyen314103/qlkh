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

    $countSql = "SELECT COUNT(*) FROM inbound_orders";
    $params = [];
    if (!empty($searchTerm)) {
        $countSql .= " WHERE supplier_info LIKE :searchTerm";
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));

    $sql = "SELECT o.*, GROUP_CONCAT(CONCAT(d.product_name, ' (SKU: ', d.sku, ', Số lượng: ', d.quantity_purchased, ')') SEPARATOR '<br>') AS details
            FROM inbound_orders o
            LEFT JOIN inbound_order_details d ON o.id = d.order_id";
    if (!empty($searchTerm)) {
        $sql .= " WHERE o.supplier_info LIKE :searchTerm";
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
        $tableHtml = '<tr><td colspan="5" class="text-center">Không tìm thấy đơn nhập kho</td></tr>';
    } else {
        foreach ($orders as $order) {
            $tableHtml .= "<tr>";
            $tableHtml .= "<td>" . nl2br(htmlspecialchars($order['supplier_info'])) . "</td>";
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

if ($action === 'suggestProduct') {
    $term = '%' . ($_POST['term'] ?? '') . '%';
    $stmt = $conn->prepare("SELECT id, name, sku, purchase_price FROM products WHERE name LIKE :term LIMIT 5");
    $stmt->bindParam(':term', $term);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
    exit;
}

if ($action === 'getProduct') {
    $sku = $_POST['sku'] ?? '';
    $stmt = $conn->prepare("SELECT id, name, sku, purchase_price FROM products WHERE sku = :sku");
    $stmt->bindParam(':sku', $sku);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
    exit;
}

if ($action === 'get') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM inbound_orders WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM inbound_order_details WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $id);
    $stmt->execute();
    $order['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($order);
    exit;
}

if ($action === 'add') {
    $supplierInfo = $_POST['supplierInfo'] ?? '';
    $purchaseDate = $_POST['purchaseDate'] ?? '';
    $products = $_POST['products'] ?? [];
    $invoiceFile = $_FILES['invoiceFile'] ?? null;
    $invoiceFileName = null;

    if (empty($supplierInfo) || empty($purchaseDate)) {
        echo "Vui lòng nhập đầy đủ thông tin nhà cung cấp và ngày nhập!";
        exit;
    }

    $totalAmount = 0;
    foreach ($products as $product) {
        $sku = $product['sku'] ?? '';
        $quantityPurchased = intval($product['quantity']) ?? 0;
        if ($quantityPurchased <= 0) {
            echo "Số lượng nhập phải lớn hơn 0!";
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, purchase_price FROM products WHERE sku = :sku");
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            echo "Sản phẩm với SKU $sku không tồn tại!";
            exit;
        }
        $totalAmount += $quantityPurchased * $prod['purchase_price'];
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO inbound_orders (supplier_info, total_amount, purchase_date) VALUES (:supplierInfo, :totalAmount, :purchaseDate)");
        $stmt->bindParam(':supplierInfo', $supplierInfo);
        $stmt->bindParam(':totalAmount', $totalAmount);
        $stmt->bindParam(':purchaseDate', $purchaseDate);
        $stmt->execute();
        $orderId = $conn->lastInsertId();

        if ($invoiceFile && $invoiceFile['size'] > 0) {
            $allowedExtensions = ['pdf', 'png', 'jpg'];
            $ext = strtolower(pathinfo($invoiceFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                throw new Exception("Chỉ chấp nhận file .pdf, .png, .jpg!");
            }
            $formattedDate = str_replace('-', '', $purchaseDate);
            $invoiceFileName = "hoadon_nhap_{$formattedDate}_{$orderId}.{$ext}";
            $destination = $invoicesDir . $invoiceFileName;
            if (!move_uploaded_file($invoiceFile['tmp_name'], $destination)) {
                throw new Exception("Lỗi khi tải lên hóa đơn!");
            }
            $stmt = $conn->prepare("UPDATE inbound_orders SET invoice_file = :invoiceFile WHERE id = :orderId");
            $stmt->bindParam(':invoiceFile', $invoiceFileName);
            $stmt->bindParam(':orderId', $orderId);
            $stmt->execute();
        }

        foreach ($products as $product) {
            $sku = $product['sku'];
            $quantityPurchased = intval($product['quantity']);
            $stmt = $conn->prepare("SELECT id, name FROM products WHERE sku = :sku");
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("INSERT INTO inbound_order_details (order_id, product_id, product_name, sku, quantity_purchased) VALUES (:orderId, :productId, :productName, :sku, :quantityPurchased)");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':productId', $prod['id']);
            $stmt->bindParam(':productName', $prod['name']);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':quantityPurchased', $quantityPurchased);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + :quantityPurchased WHERE sku = :sku");
            $stmt->bindParam(':quantityPurchased', $quantityPurchased);
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
        echo "Lỗi khi thêm đơn nhập kho: " . $e->getMessage();
    }
    exit;
}

if ($action === 'update') {
    $orderId = $_POST['orderId'] ?? 0;
    $supplierInfo = $_POST['supplierInfo'] ?? '';
    $purchaseDate = $_POST['purchaseDate'] ?? '';
    $products = $_POST['products'] ?? [];
    $invoiceFile = $_FILES['invoiceFile'] ?? null;
    $invoiceFileName = null;

    if (empty($supplierInfo) || empty($purchaseDate)) {
        echo "Vui lòng nhập đầy đủ thông tin nhà cung cấp và ngày nhập!";
        exit;
    }

    $stmt = $conn->prepare("SELECT invoice_file, purchase_date FROM inbound_orders WHERE id = :orderId");
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    $oldOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldInvoiceFile = $oldOrder['invoice_file'] ?? null;

    if ($invoiceFile && $invoiceFile['size'] > 0) {
        $allowedExtensions = ['pdf', 'png', 'jpg'];
        $ext = strtolower(pathinfo($invoiceFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            echo "Chỉ chấp nhận file .pdf, .png, .jpg!";
            exit;
        }
        $formattedDate = str_replace('-', '', $purchaseDate);
        $invoiceFileName = "hoadon_nhap_{$formattedDate}_{$orderId}.{$ext}";
        $destination = $invoicesDir . $invoiceFileName;
        if (!move_uploaded_file($invoiceFile['tmp_name'], $destination)) {
            echo "Lỗi khi tải lên hóa đơn!";
            exit;
        }
        if ($oldInvoiceFile && file_exists($invoicesDir . $oldInvoiceFile)) {
            unlink($invoicesDir . $oldInvoiceFile);
        }
    } else {
        $invoiceFileName = $oldInvoiceFile;
        if ($oldInvoiceFile && $purchaseDate !== $oldOrder['purchase_date']) {
            $ext = pathinfo($oldInvoiceFile, PATHINFO_EXTENSION);
            $newFileName = "hoadon_nhap_" . str_replace('-', '', $purchaseDate) . "_{$orderId}.{$ext}";
            if (file_exists($invoicesDir . $oldInvoiceFile)) {
                rename($invoicesDir . $oldInvoiceFile, $invoicesDir . $newFileName);
            }
            $invoiceFileName = $newFileName;
        }
    }

    $stmt = $conn->prepare("SELECT product_id, quantity_purchased FROM inbound_order_details WHERE order_id = :orderId");
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    $oldDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalAmount = 0;
    foreach ($products as $product) {
        $sku = $product['sku'] ?? '';
        $quantityPurchased = intval($product['quantity']) ?? 0;
        if ($quantityPurchased <= 0) {
            echo "Số lượng nhập phải lớn hơn 0!";
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, purchase_price FROM products WHERE sku = :sku");
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            echo "Sản phẩm với SKU $sku không tồn tại!";
            exit;
        }
        $totalAmount += $quantityPurchased * $prod['purchase_price'];
    }

    try {
        $conn->beginTransaction();

        foreach ($oldDetails as $detail) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - :quantityPurchased WHERE id = :productId");
            $stmt->bindParam(':quantityPurchased', $detail['quantity_purchased']);
            $stmt->bindParam(':productId', $detail['product_id']);
            $stmt->execute();
        }

        $stmt = $conn->prepare("UPDATE inbound_orders SET supplier_info = :supplierInfo, total_amount = :totalAmount, purchase_date = :purchaseDate, invoice_file = :invoiceFile WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->bindParam(':supplierInfo', $supplierInfo);
        $stmt->bindParam(':totalAmount', $totalAmount);
        $stmt->bindParam(':purchaseDate', $purchaseDate);
        $stmt->bindParam(':invoiceFile', $invoiceFileName);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM inbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        foreach ($products as $product) {
            $sku = $product['sku'];
            $quantityPurchased = intval($product['quantity']);
            $stmt = $conn->prepare("SELECT id, name FROM products WHERE sku = :sku");
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("INSERT INTO inbound_order_details (order_id, product_id, product_name, sku, quantity_purchased) VALUES (:orderId, :productId, :productName, :sku, :quantityPurchased)");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':productId', $prod['id']);
            $stmt->bindParam(':productName', $prod['name']);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':quantityPurchased', $quantityPurchased);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + :quantityPurchased WHERE sku = :sku");
            $stmt->bindParam(':quantityPurchased', $quantityPurchased);
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
        echo "Lỗi khi cập nhật đơn nhập kho: " . $e->getMessage();
    }
    exit;
}

if ($action === 'delete') {
    $orderId = $_POST['id'] ?? 0;

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT invoice_file FROM inbound_orders WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();
        $invoiceFile = $stmt->fetchColumn();

        $stmt = $conn->prepare("SELECT product_id, quantity_purchased FROM inbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - :quantityPurchased WHERE id = :productId");
            $stmt->bindParam(':quantityPurchased', $detail['quantity_purchased']);
            $stmt->bindParam(':productId', $detail['product_id']);
            $stmt->execute();
        }

        $stmt = $conn->prepare("DELETE FROM inbound_order_details WHERE order_id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM inbound_orders WHERE id = :orderId");
        $stmt->bindParam(':orderId', $orderId);
        $stmt->execute();

        if ($invoiceFile && file_exists($invoicesDir . $invoiceFile)) {
            unlink($invoicesDir . $invoiceFile);
        }

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Lỗi khi xóa đơn nhập kho: " . $e->getMessage();
    }
    exit;
}
?>