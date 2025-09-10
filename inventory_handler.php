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

if ($action === 'load') {
    $itemsPerPage = 20;
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $offset = ($page - 1) * $itemsPerPage;
    $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

    // Count total items for pagination
    $countSql = "SELECT COUNT(*) FROM products";
    $params = [];
    if (!empty($searchTerm)) {
        $countSql .= " WHERE name LIKE :searchTerm OR sku LIKE :searchTerm";
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));

    // Fetch paginated data
    $sql = "SELECT * FROM products";
    if (!empty($searchTerm)) {
        $sql .= " WHERE name LIKE :searchTerm OR sku LIKE :searchTerm";
    }
    $sql .= " ORDER BY id DESC LIMIT :offset, :itemsPerPage";
    $stmt = $conn->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tableHtml = '';
    if (empty($products)) {
        $tableHtml = '<tr><td colspan="6" class="text-center">Không tìm thấy sản phẩm</td></tr>';
    } else {
        foreach ($products as $product) {
            $tableHtml .= "<tr>";
            $tableHtml .= "<td>" . htmlspecialchars($product['name']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($product['sku']) . "</td>";
            $tableHtml .= "<td>" . number_format($product['purchase_price'], 0, ',', '.') . "</td>";
            $tableHtml .= "<td>" . number_format($product['sale_price'], 0, ',', '.') . "</td>";
            $tableHtml .= "<td>" . $product['quantity'] . "</td>";
            $tableHtml .= "<td>";
            $tableHtml .= "<button class='btn btn-warning btn-sm edit-btn me-1' data-id='" . $product['id'] . "'>Sửa</button>";
            $tableHtml .= "<button class='btn btn-danger btn-sm delete-btn' data-id='" . $product['id'] . "'>Xóa</button>";
            $tableHtml .= "</td>";
            $tableHtml .= "</tr>";
        }
    }

    echo json_encode(['table' => $tableHtml, 'totalPages' => $totalPages]);
    exit;
}

if ($action === 'suggest') {
    $term = '%' . ($_POST['term'] ?? '') . '%';
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :term LIMIT 5");
    $stmt->bindParam(':term', $term);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
    exit;
}

if ($action === 'get') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
    exit;
}

if ($action === 'add') {
    $name = $_POST['name'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $purchasePrice = $_POST['purchasePrice'] ?? 0;
    $salePrice = $_POST['salePrice'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;

    // Check for duplicate SKU
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE sku = :sku");
    $stmt->bindParam(':sku', $sku);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "SKU đã tồn tại!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products (name, sku, purchase_price, sale_price, quantity) VALUES (:name, :sku, :purchasePrice, :salePrice, :quantity)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':purchasePrice', $purchasePrice);
    $stmt->bindParam(':salePrice', $salePrice);
    $stmt->bindParam(':quantity', $quantity);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi thêm sản phẩm!";
    }
    exit;
}

if ($action === 'update') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $purchasePrice = $_POST['purchasePrice'] ?? 0;
    $salePrice = $_POST['salePrice'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;

    // Check for duplicate SKU (excluding current product)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE sku = :sku AND id != :id");
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "SKU đã tồn tại!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name = :name, sku = :sku, purchase_price = :purchasePrice, sale_price = :salePrice, quantity = :quantity WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':purchasePrice', $purchasePrice);
    $stmt->bindParam(':salePrice', $salePrice);
    $stmt->bindParam(':quantity', $quantity);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi cập nhật sản phẩm!";
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi xóa sản phẩm!";
    }
    exit;
}
?>