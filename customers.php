<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            margin-top: 20px;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Hệ Thống Quản Lý Kho</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link active" href="customers.php">Quản Lý Khách Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="inventory.php">Quản Lý Kho Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="outbound.php">Quản Lý Xuất Kho</a></li>
                    <li class="nav-item"><a class="nav-link" href="inbound.php">Quản Lý Nhập Kho</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Quản Lý Khách Hàng</h1>

        <!-- Customer Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thêm/Sửa Khách Hàng</h5>
                <form id="customerForm">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="customerId" class="form-label">Mã Khách Hàng</label>
                            <input type="text" class="form-control" id="customerId" name="customerId" required>
                        </div>
                        <div class="col-md-2">
                            <label for="fullName" class="form-label">Họ Tên</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                        </div>
                        <div class="col-md-2">
                            <label for="phone" class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-2">
                            <label for="address" class="form-label">Địa Chỉ</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 mt-2" id="submitBtn">Thêm</button>
                            <input type="hidden" id="id" name="id">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo họ tên, mã khách hàng hoặc số điện thoại">
            </div>
        </div>

        <!-- Customer Table -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Mã Khách Hàng</th>
                        <th>Họ Tên</th>
                        <th>Số Điện Thoại</th>
                        <th>Địa Chỉ</th>
                        <th>Email</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="customerTable"></tbody>
            </table>
            <nav aria-label="Page navigation">
                <ul class="pagination" id="pagination"></ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            let totalPages = 1;
            let lastSearchTerm = '';

            // Generate random customer ID
            function generateCustomerId() {
                let randomNum = Math.floor(1000 + Math.random() * 9000); // Random 4-digit number
                return 'KH' + randomNum;
            }

            // Check if customer ID is unique
            function checkUniqueCustomerId(customerId, callback) {
                $.ajax({
                    url: 'customers_handler.php',
                    method: 'POST',
                    data: { action: 'checkCustomerId', customerId: customerId },
                    success: function(response) {
                        if (response === 'exists') {
                            // If ID exists, generate a new one and check again
                            let newCustomerId = generateCustomerId();
                            checkUniqueCustomerId(newCustomerId, callback);
                        } else {
                            callback(customerId);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX kiểm tra mã:', status, error);
                        callback(customerId); // Fallback to current ID on error
                    }
                });
            }

            // Load customers with pagination
            function loadCustomers(page = 1, searchTerm = '') {
                $.ajax({
                    url: 'customers_handler.php',
                    method: 'POST',
                    data: { action: 'load', page: page, searchTerm: searchTerm },
                    dataType: 'json',
                    success: function(response) {
                        $('#customerTable').html(response.table || '<tr><td colspan="6" class="text-center">Không tìm thấy khách hàng</td></tr>');
                        totalPages = response.totalPages || 1;
                        updatePagination(page);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX:', status, error);
                        $('#customerTable').html('<tr><td colspan="6" class="text-center">Lỗi tải dữ liệu</td></tr>');
                    }
                });
            }

            // Update pagination controls
            function updatePagination(currentPage) {
                let paginationHtml = '';
                if (totalPages > 1) {
                    paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Trước</a>
                    </li>`;
                    for (let i = 1; i <= totalPages; i++) {
                        paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
                    }
                    paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Sau</a>
                    </li>`;
                }
                $('#pagination').html(paginationHtml);
            }

            // Pagination click event
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    loadCustomers(page, lastSearchTerm);
                }
            });

            // Initial load
            loadCustomers();
            // Generate customer ID for new customer
            checkUniqueCustomerId(generateCustomerId(), function(uniqueId) {
                $('#customerId').val(uniqueId);
            });

            // Search customers with debounce
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                lastSearchTerm = $(this).val();
                currentPage = 1;
                searchTimeout = setTimeout(() => {
                    loadCustomers(1, lastSearchTerm);
                }, 300);
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: 'customers_handler.php',
                    method: 'POST',
                    data: { action: 'get', id: id },
                    dataType: 'json',
                    success: function(customer) {
                        $('#id').val(customer.id);
                        $('#customerId').val(customer.customer_id);
                        $('#fullName').val(customer.full_name);
                        $('#phone').val(customer.phone);
                        $('#address').val(customer.address);
                        $('#email').val(customer.email);
                        $('#submitBtn').text('Cập Nhật');
                        $('html, body').animate({ scrollTop: $('#customerForm').offset().top }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX sửa:', status, error);
                        alert('Đã xảy ra lỗi khi lấy dữ liệu khách hàng. Vui lòng thử lại.');
                    }
                });
            });

            // Clear form after submission
            function clearForm() {
                $('#customerForm')[0].reset();
                $('#id').val('');
                $('#submitBtn').text('Thêm');
                checkUniqueCustomerId(generateCustomerId(), function(uniqueId) {
                    $('#customerId').val(uniqueId);
                });
            }

            // Form submission
            $('#customerForm').on('submit', function(e) {
                e.preventDefault();
                let action = $('#id').val() ? 'update' : 'add';
                $.ajax({
                    url: 'customers_handler.php',
                    method: 'POST',
                    data: {
                        action: action,
                        id: $('#id').val(),
                        customerId: $('#customerId').val(),
                        fullName: $('#fullName').val(),
                        phone: $('#phone').val(),
                        address: $('#address').val(),
                        email: $('#email').val()
                    },
                    success: function(response) {
                        if (response.includes('success')) {
                            loadCustomers(currentPage, lastSearchTerm);
                            clearForm();
                            alert('Thao tác thành công!');
                        } else {
                            alert(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX gửi form:', status, error);
                        alert('Đã xảy ra lỗi khi gửi dữ liệu. Vui lòng thử lại.');
                    }
                });
            });

            // Delete customer
            $(document).on('click', '.delete-btn', function() {
                if (confirm('Bạn có chắc muốn xóa khách hàng này?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: 'customers_handler.php',
                        method: 'POST',
                        data: { action: 'delete', id: id },
                        success: function(response) {
                            if (response === 'success') {
                                loadCustomers(currentPage, lastSearchTerm);
                                alert('Xóa khách hàng thành công!');
                            } else {
                                alert(response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Lỗi AJAX xóa:', status, error);
                            alert('Đã xảy ra lỗi khi xóa khách hàng. Vui lòng thử lại.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>