<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Hệ Thống Quản Lý Kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        }
        .card-icon {
            font-size: 2.5rem;
            color: #fff;
            background: linear-gradient(45deg, #007bff, #00d4ff);
            padding: 20px;
            border-radius: 50%;
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
        .quick-link {
            text-decoration: none;
            color: #fff;
            background: linear-gradient(45deg, #007bff, #00d4ff);
            padding: 12px 24px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .quick-link:hover {
            background: linear-gradient(45deg, #0056b3, #0099cc);
            color: #fff;
        }
        .footer {
            background: #343a40;
            color: #fff;
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="fas fa-warehouse me-2"></i>Hệ Thống Quản Lý Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="customers.php">Quản Lý Khách Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="inventory.php">Quản Lý Kho Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="outbound.php">Quản Lý Xuất Kho</a></li>
                    <li class="nav-item"><a class="nav-link" href="inbound.php">Quản Lý Nhập Kho</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Tổng Quan Hệ Thống Quản Lý Kho</h1>

        <!-- Dashboard Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3"><i class="fas fa-boxes"></i></div>
                        <div>
                            <div class="card-title">Sản Phẩm Trong Kho</div>
                            <div class="card-value" id="totalProducts">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3"><i class="fas fa-truck-loading"></i></div>
                        <div>
                            <div class="card-title">Đơn Nhập Kho</div>
                            <div class="card-value" id="totalInbound">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3"><i class="fas fa-dolly"></i></div>
                        <div>
                            <div class="card-title">Đơn Xuất Kho</div>
                            <div class="card-value" id="totalOutbound">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="card-title">Khách Hàng</div>
                            <div class="card-value" id="totalCustomers">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p class="mb-0">&copy; 2025 Hệ Thống Quản Lý Kho. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load dashboard stats
            function loadStats() {
                $.ajax({
                    url: 'dashboard_handler.php',
                    method: 'POST',
                    data: { action: 'getStats' },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Stats response:', response); // Debug
                        if (response.error) {
                            alert('Lỗi: ' + response.error);
                            return;
                        }
                        $('#totalProducts').text(response.totalProducts || 0);
                        $('#totalInbound').text(response.totalInbound || 0);
                        $('#totalOutbound').text(response.totalOutbound || 0);
                        $('#totalCustomers').text(response.totalCustomers || 0);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX load stats:', status, error, xhr.responseText);
                        alert('Lỗi tải dữ liệu thống kê. Vui lòng kiểm tra Console (F12).');
                    }
                });
            }

            // Initialize
            loadStats();
        });
    </script>
</body>
</html>