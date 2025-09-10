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
    $itemsPerPage = 10;
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $offset = ($page - 1) * $itemsPerPage;
    $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

    // Count total items for pagination
    $countSql = "SELECT COUNT(*) FROM customers";
    $params = [];
    if (!empty($searchTerm)) {
        $countSql .= " WHERE full_name LIKE :searchTerm OR customer_id LIKE :searchTerm OR phone LIKE :searchTerm";
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));

    // Fetch paginated data
    $sql = "SELECT * FROM customers";
    if (!empty($searchTerm)) {
        $sql .= " WHERE full_name LIKE :searchTerm OR customer_id LIKE :searchTerm OR phone LIKE :searchTerm";
    }
    $sql .= " LIMIT :offset, :itemsPerPage";
    $stmt = $conn->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tableHtml = '';
    if (empty($customers)) {
        $tableHtml = '<tr><td colspan="6" class="text-center">Không tìm thấy khách hàng</td></tr>';
    } else {
        foreach ($customers as $customer) {
            $tableHtml .= "<tr>";
            $tableHtml .= "<td>" . htmlspecialchars($customer['customer_id']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($customer['full_name']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($customer['phone']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($customer['address']) . "</td>";
            $tableHtml .= "<td>" . htmlspecialchars($customer['email']) . "</td>";
            $tableHtml .= "<td>";
            $tableHtml .= "<button class='btn btn-warning btn-sm edit-btn me-1' data-id='" . $customer['id'] . "'>Sửa</button>";
            $tableHtml .= "<button class='btn btn-danger btn-sm delete-btn' data-id='" . $customer['id'] . "'>Xóa</button>";
            $tableHtml .= "</td>";
            $tableHtml .= "</tr>";
        }
    }

    echo json_encode(['table' => $tableHtml, 'totalPages' => $totalPages]);
    exit;
}

if ($action === 'get') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($customer);
    exit;
}

if ($action === 'checkCustomerId') {
    $customerId = $_POST['customerId'] ?? '';
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :customerId");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->execute();
    echo $stmt->fetchColumn() > 0 ? 'exists' : 'unique';
    exit;
}

if ($action === 'add') {
    $customerId = $_POST['customerId'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';

    // Check for duplicate customer ID
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :customerId");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "Mã khách hàng đã tồn tại!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone, address, email) VALUES (:customerId, :fullName, :phone, :address, :email)");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->bindParam(':fullName', $fullName);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':email', $email);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi thêm khách hàng!";
    }
    exit;
}

if ($action === 'update') {
    $id = $_POST['id'] ?? 0;
    $customerId = $_POST['customerId'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';

    // Check for duplicate customer ID (excluding current customer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :customerId AND id != :id");
    $stmt->bindParam(':customerId', $customerId);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "Mã khách hàng đã tồn tại!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE customers SET customer_id = :customerId, full_name = :fullName, phone = :phone, address = :address, email = :email WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':customerId', $customerId);
    $stmt->bindParam(':fullName', $fullName);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':email', $email);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi cập nhật khách hàng!";
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = :id");
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi xóa khách hàng!";
    }
    exit;
}
?>