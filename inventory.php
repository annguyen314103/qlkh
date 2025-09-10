<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kho Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            margin-top: 20px;
        }
        .suggestion-list {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            display: none;
        }
        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
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
                    <li class="nav-item"><a class="nav-link" href="customers.php">Quản Lý Khách Hàng</a></li>
                    <li class="nav-item"><a class="nav-link active" href="inventory.php">Quản Lý Kho Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="outbound.php">Quản Lý Xuất Kho</a></li>
                    <li class="nav-item"><a class="nav-link" href="inbound.php">Quản Lý Nhập Kho</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Quản Lý Kho Hàng</h1>

        <!-- Product Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thêm/Sửa Sản Phẩm</h5>
                <form id="productForm">
                    <div class="row">
                        <div class="col-md-3 position-relative">
                            <label for="productName" class="form-label">Tên Sản Phẩm</label>
                            <input type="text" class="form-control" id="productName" name="productName" required>
                            <div id="suggestionList" class="suggestion-list"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" required>
                        </div>
                        <div class="col-md-2">
                            <label for="purchasePrice" class="form-label">Giá Nhập</label>
                            <input type="number" class="form-control" id="purchasePrice" name="purchasePrice" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label for="salePrice" class="form-label">Giá Bán</label>
                            <input type="number" class="form-control" id="salePrice" name="salePrice" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label for="quantity" class="form-label">Số Lượng</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100" id="submitBtn">Thêm</button>
                            <button type="button" class="btn btn-secondary w-100" id="cancelBtn" style="display: none;">Hủy bỏ</button>
                            <input type="hidden" id="productId" name="productId">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo tên sản phẩm hoặc SKU">
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tên Sản Phẩm</th>
                        <th>SKU</th>
                        <th>Giá Nhập</th>
                        <th>Giá Bán</th>
                        <th>Số Lượng</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="productTable"></tbody>
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

            // Function to trim pasted input
            function handlePaste(event, inputElement) {
                event.preventDefault();
                let pastedData = (event.originalEvent || event).clipboardData.getData('text');
                let trimmedData = pastedData.trim();
                $(inputElement).val(trimmedData);
                // Trigger input event to ensure search or suggestion logic is executed
                $(inputElement).trigger('input');
            }

            // Add paste event listeners to inputs
            $('#searchInput').on('paste', function(e) {
                handlePaste(e, this);
            });
            $('#productName').on('paste', function(e) {
                handlePaste(e, this);
            });
            $('#sku').on('paste', function(e) {
                handlePaste(e, this);
            });

            // Load products with pagination
            function loadProducts(page = 1, searchTerm = '') {
                $.ajax({
                    url: 'inventory_handler.php',
                    method: 'POST',
                    data: { action: 'load', page: page, searchTerm: searchTerm },
                    dataType: 'json',
                    success: function(response) {
                        $('#productTable').html(response.table || '<tr><td colspan="6" class="text-center">Không tìm thấy sản phẩm</td></tr>');
                        totalPages = response.totalPages || 1;
                        updatePagination(page);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX:', status, error);
                        $('#productTable').html('<tr><td colspan="6" class="text-center">Lỗi tải dữ liệu</td></tr>');
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
                    loadProducts(page, lastSearchTerm);
                }
            });

            // Initial load
            loadProducts();

            // Search products with debounce
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                lastSearchTerm = $(this).val();
                currentPage = 1;
                searchTimeout = setTimeout(() => {
                    loadProducts(1, lastSearchTerm);
                }, 300);
            });

            // Auto-suggest product names
            $('#productName').on('input', function() {
                let term = $(this).val();
                if (term.length > 0) {
                    $.ajax({
                        url: 'inventory_handler.php',
                        method: 'POST',
                        data: { action: 'suggest', term: term },
                        dataType: 'json',
                        success: function(response) {
                            let suggestions = response;
                            let suggestionHtml = '';
                            suggestions.forEach(function(product) {
                                suggestionHtml += `<div class="suggestion-item" data-id="${product.id}" data-name="${product.name}" data-sku="${product.sku}" data-purchase="${product.purchase_price}" data-sale="${product.sale_price}" data-quantity="${product.quantity}">${product.name} (SKU: ${product.sku})</div>`;
                            });
                            $('#suggestionList').html(suggestionHtml).show();
                        },
                        error: function(xhr, status, error) {
                            console.error('Lỗi AJAX gợi ý:', status, error);
                        }
                    });
                } else {
                    $('#suggestionList').hide();
                }
            });

            // Select suggestion
            $(document).on('click', '.suggestion-item', function() {
                $('#productId').val($(this).data('id'));
                $('#productName').val($(this).data('name'));
                $('#sku').val($(this).data('sku'));
                $('#purchasePrice').val($(this).data('purchase'));
                $('#salePrice').val($(this).data('sale'));
                $('#quantity').val($(this).data('quantity'));
                $('#submitBtn').text('Cập Nhật');
                $('#cancelBtn').show();
                $('#suggestionList').hide();
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: 'inventory_handler.php',
                    method: 'POST',
                    data: { action: 'get', id: id },
                    dataType: 'json',
                    success: function(product) {
                        $('#productId').val(product.id);
                        $('#productName').val(product.name);
                        $('#sku').val(product.sku);
                        $('#purchasePrice').val(product.purchase_price);
                        $('#salePrice').val(product.sale_price);
                        $('#quantity').val(product.quantity);
                        $('#submitBtn').text('Cập Nhật');
                        $('#cancelBtn').show();
                        $('html, body').animate({ scrollTop: $('#productForm').offset().top }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX sửa:', status, error);
                        alert('Đã xảy ra lỗi khi lấy dữ liệu sản phẩm. Vui lòng thử lại.');
                    }
                });
            });

            // Cancel button click
            $(document).on('click', '#cancelBtn', function() {
                $('#productForm')[0].reset();
                $('#productId').val('');
                $('#submitBtn').text('Thêm');
                $('#cancelBtn').hide();
            });

            // Hide suggestion list when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#productName, #suggestionList').length) {
                    $('#suggestionList').hide();
                }
            });

            // Clear form after submission
            function clearForm() {
                $('#productForm')[0].reset();
                $('#productId').val('');
                $('#submitBtn').text('Thêm');
                $('#cancelBtn').hide();
            }

            // Form submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                let action = $('#productId').val() ? 'update' : 'add';
                $.ajax({
                    url: 'inventory_handler.php',
                    method: 'POST',
                    data: {
                        action: action,
                        id: $('#productId').val(),
                        name: $('#productName').val(),
                        sku: $('#sku').val(),
                        purchasePrice: $('#purchasePrice').val(),
                        salePrice: $('#salePrice').val(),
                        quantity: $('#quantity').val()
                    },
                    success: function(response) {
                        if (response.includes('success')) {
                            loadProducts(currentPage, lastSearchTerm);
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

            // Delete product
            $(document).on('click', '.delete-btn', function() {
                if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: 'inventory_handler.php',
                        method: 'POST',
                        data: { action: 'delete', id: id },
                        success: function(response) {
                            if (response === 'success') {
                                loadProducts(currentPage, lastSearchTerm);
                                alert('Xóa sản phẩm thành công!');
                            } else {
                                alert(response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Lỗi AJAX xóa:', status, error);
                            alert('Đã xảy ra lỗi khi xóa sản phẩm. Vui lòng thử lại.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>