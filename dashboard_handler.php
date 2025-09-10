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
    echo json_encode(['error' => "Kết nối database thất bại: " . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'getStats') {
    $response = [
        'totalProducts' => 0,
        'totalInbound' => 0,
        'totalOutbound' => 0,
        'totalCustomers' => 0
    ];

    try {
        // Total Products
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products");
        $stmt->execute();
        $response['totalProducts'] = $stmt->fetchColumn();

        // Total Inbound Orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM inbound_orders");
        $stmt->execute();
        $response['totalInbound'] = $stmt->fetchColumn();

        // Total Outbound Orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM outbound_orders");
        $stmt->execute();
        $response['totalOutbound'] = $stmt->fetchColumn();

        // Total Customers
        $stmt = $conn->prepare("SELECT COUNT(*) FROM customers");
        $stmt->execute();
        $response['totalCustomers'] = $stmt->fetchColumn();

        echo json_encode($response);
    } catch(PDOException $e) {
        echo json_encode(['error' => "Lỗi truy vấn database: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Hành động không hợp lệ']);
exit;
?>