<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhập Kho</title>
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
        .product-row {
            margin-bottom: 10px;
        }
        .product-row .remove-product {
            margin-top: 31px;
        }
        .btn{
            text-transform: uppercase;
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
                    <li class="nav-item"><a class="nav-link" href="inventory.php">Quản Lý Kho Hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="outbound.php">Quản Lý Xuất Kho</a></li>
                    <li class="nav-item"><a class="nav-link active" href="inbound.php">Quản Lý Nhập Kho</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Quản Lý Nhập Kho</h1>

        <!-- Order Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thêm/Sửa Đơn Nhập Kho</h5>
                <form id="orderForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="supplierInfo" class="form-label">Thông tin nhà cung cấp</label>
                            <textarea class="form-control" id="supplierInfo" name="supplierInfo" rows="4" required>Tên: 
SĐT: 
Link: 
</textarea>
                        </div>
                        <div class="col-md-3">
                            <label for="purchaseDate" class="form-label">Ngày Nhập</label>
                            <input type="date" class="form-control" id="purchaseDate" name="purchaseDate" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="invoiceFile" class="form-label">Hóa Đơn Điện Tử (.pdf, .png, .jpg)</label>
                            <input type="file" class="form-control" id="invoiceFile" name="invoiceFile" accept=".pdf,.png,.jpg">
                            <small id="currentInvoice" class="form-text text-muted"></small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Chi Tiết Đơn Nhập Kho</h6>
                        <div id="productList">
                            <div class="row product-row" data-index="0">
                                <div class="col-md-4 position-relative">
                                    <label for="productName0" class="form-label">Tên Sản Phẩm</label>
                                    <input type="text" class="form-control product-name" id="productName0" name="products[0][name]" required>
                                    <div class="suggestion-list" id="suggestionList0"></div>
                                </div>
                                <div class="col-md-2">
                                    <label for="sku0" class="form-label">SKU</label>
                                    <input type="text" class="form-control product-sku" id="sku0" name="products[0][sku]" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="quantity0" class="form-label">Số Lượng Nhập</label>
                                    <input type="number" class="form-control product-quantity" id="quantity0" name="products[0][quantity]" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger remove-product">Xóa</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="addProduct">Thêm Sản Phẩm</button>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="totalAmount" class="form-label">Tổng Tiền</label>
                            <input type="text" class="form-control" id="totalAmount" name="totalAmount" readonly>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" id="submitBtn">Thêm đơn nhập kho</button>
                            <input type="hidden" id="orderId" name="orderId">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo thông tin nhà cung cấp">
            </div>
        </div>

        <!-- Order Table -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Thông tin nhà cung cấp</th>
                        <th>Chi Tiết Đơn Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Ngày Nhập</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="orderTable"></tbody>
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
            let productCount = 1;

            // Set default purchase date to today
            const today = new Date().toISOString().split('T')[0];
            $('#purchaseDate').val(today);

            // Load orders with pagination
            function loadOrders(page = 1, searchTerm = '') {
                $.ajax({
                    url: 'inbound_handler.php',
                    method: 'POST',
                    data: { action: 'load', page: page, searchTerm: searchTerm },
                    dataType: 'json',
                    success: function(response) {
                        $('#orderTable').html(response.table || '<tr><td colspan="5" class="text-center">Không tìm thấy đơn nhập kho</td></tr>');
                        totalPages = response.totalPages || 1;
                        updatePagination(page);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX load:', status, error);
                        $('#orderTable').html('<tr><td colspan="5" class="text-center">Lỗi tải dữ liệu</td></tr>');
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
                    loadOrders(page, lastSearchTerm);
                }
            });

            // Initial load
            loadOrders();

            // Search orders with debounce
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                lastSearchTerm = $(this).val();
                currentPage = 1;
                searchTimeout = setTimeout(() => {
                    loadOrders(1, lastSearchTerm);
                }, 300);
            });

            // Add product row
            $('#addProduct').on('click', function() {
                let index = productCount++;
                let productRow = `
                    <div class="row product-row" data-index="${index}">
                        <div class="col-md-4 position-relative">
                            <label for="productName${index}" class="form-label">Tên Sản Phẩm</label>
                            <input type="text" class="form-control product-name" id="productName${index}" name="products[${index}][name]" required>
                            <div class="suggestion-list" id="suggestionList${index}"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="sku${index}" class="form-label">SKU</label>
                            <input type="text" class="form-control product-sku" id="sku${index}" name="products[${index}][sku]" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="quantity${index}" class="form-label">Số Lượng Nhập</label>
                            <input type="number" class="form-control product-quantity" id="quantity${index}" name="products[${index}][quantity]" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger remove-product">Xóa</button>
                        </div>
                    </div>`;
                $('#productList').append(productRow);
                calculateTotalAmount();
            });

            // Remove product row
            $(document).on('click', '.remove-product', function() {
                if ($('.product-row').length > 1) {
                    $(this).closest('.product-row').remove();
                    calculateTotalAmount();
                }
            });

            // Auto-suggest product names
            $(document).on('input', '.product-name', function() {
                let index = $(this).closest('.product-row').data('index');
                let term = $(this).val();
                console.log('Searching for product:', term, 'Index:', index);
                if (term.length > 0) {
                    $.ajax({
                        url: 'inbound_handler.php',
                        method: 'POST',
                        data: { action: 'suggestProduct', term: term },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Suggestions received:', response);
                            let suggestions = response;
                            let suggestionHtml = '';
                            if (suggestions.length === 0) {
                                suggestionHtml = '<div class="suggestion-item">Không tìm thấy sản phẩm</div>';
                            } else {
                                suggestions.forEach(function(product) {
                                    suggestionHtml += `<div class="suggestion-item" data-id="${product.id}" data-name="${product.name}" data-sku="${product.sku}" data-purchase-price="${product.purchase_price}">${product.name} (SKU: ${product.sku})</div>`;
                                });
                            }
                            $(`#suggestionList${index}`).html(suggestionHtml).show();
                        },
                        error: function(xhr, status, error) {
                            console.error('Lỗi AJAX gợi ý sản phẩm:', status, error, xhr.responseText);
                            $(`#suggestionList${index}`).html('<div class="suggestion-item">Lỗi tải gợi ý</div>').show();
                        }
                    });
                } else {
                    $(`#suggestionList${index}`).hide();
                }
            });

            // Select product suggestion
            $(document).on('click', '.suggestion-item', function() {
                let suggestionList = $(this).closest('.suggestion-list').attr('id');
                if (suggestionList.includes('suggestionList')) {
                    let index = suggestionList.replace('suggestionList', '');
                    console.log('Selected suggestion:', $(this).data('name'), 'Index:', index);
                    $(`#productName${index}`).val($(this).data('name'));
                    $(`#sku${index}`).val($(this).data('sku'));
                    $(`#suggestionList${index}`).hide();
                    calculateTotalAmount();
                }
            });

            // Calculate total amount
            function calculateTotalAmount() {
                let total = 0;
                $('.product-row').each(function() {
                    let index = $(this).data('index');
                    let quantity = parseInt($(`#quantity${index}`).val()) || 0;
                    let sku = $(`#sku${index}`).val();
                    if (sku) {
                        $.ajax({
                            url: 'inbound_handler.php',
                            method: 'POST',
                            data: { action: 'getProduct', sku: sku },
                            dataType: 'json',
                            async: false,
                            success: function(product) {
                                console.log('Product purchase price for SKU', sku, ':', product.purchase_price);
                                total += quantity * product.purchase_price;
                            },
                            error: function(xhr, status, error) {
                                console.error('Lỗi AJAX getProduct:', status, error);
                            }
                        });
                    }
                });
                $('#totalAmount').val(total.toLocaleString('vi-VN'));
            }

            // Update total amount on quantity change
            $(document).on('input', '.product-quantity', calculateTotalAmount);

            // Hide suggestion lists when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.product-name, .suggestion-list').length) {
                    $('.suggestion-list').hide();
                }
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: 'inbound_handler.php',
                    method: 'POST',
                    data: { action: 'get', id: id },
                    dataType: 'json',
                    success: function(order) {
                        console.log('Edit order:', order);
                        $('#orderId').val(order.id);
                        $('#supplierInfo').val(order.supplier_info);
                        $('#purchaseDate').val(order.purchase_date);
                        $('#currentInvoice').text(order.invoice_file ? `Hóa đơn hiện tại: ${order.invoice_file}` : '');
                        $('#totalAmount').val(parseFloat(order.total_amount).toLocaleString('vi-VN'));
                        $('#productList').empty();
                        productCount = 0;
                        order.details.forEach(function(detail, index) {
                            let productRow = `
                                <div class="row product-row" data-index="${index}">
                                    <div class="col-md-4 position-relative">
                                        <label for="productName${index}" class="form-label">Tên Sản Phẩm</label>
                                        <input type="text" class="form-control product-name" id="productName${index}" name="products[${index}][name]" value="${detail.product_name}" required>
                                        <div class="suggestion-list" id="suggestionList${index}"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="sku${index}" class="form-label">SKU</label>
                                        <input type="text" class="form-control product-sku" id="sku${index}" name="products[${index}][sku]" value="${detail.sku}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="quantity${index}" class="form-label">Số Lượng Nhập</label>
                                        <input type="number" class="form-control product-quantity" id="quantity${index}" name="products[${index}][quantity]" value="${detail.quantity_purchased}" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger remove-product">Xóa</button>
                                    </div>
                                </div>`;
                            $('#productList').append(productRow);
                            productCount = index + 1;
                        });
                        $('#submitBtn').text('Cập Nhật');
                        $('html, body').animate({ scrollTop: $('#orderForm').offset().top }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Lỗi AJAX sửa:', status, error);
                        alert('Đã xảy ra lỗi khi lấy dữ liệu đơn nhập kho. Vui lòng thử lại.');
                    }
                });
            });

            // Clear form after submission
            function clearForm() {
                $('#orderId').val('');
                $('#supplierInfo').val(`Tên:\nSĐT:\nLink:\n`);
                $('#purchaseDate').val(today);
                $('#invoiceFile').val('');
                $('#currentInvoice').text('');
                $('#productList').html(`
                    <div class="row product-row" data-index="0">
                        <div class="col-md-4 position-relative">
                            <label for="productName0" class="form-label">Tên Sản Phẩm</label>
                            <input type="text" class="form-control product-name" id="productName0" name="products[0][name]" required>
                            <div class="suggestion-list" id="suggestionList0"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="sku0" class="form-label">SKU</label>
                            <input type="text" class="form-control product-sku" id="sku0" name="products[0][sku]" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="quantity0" class="form-label">Số Lượng Nhập</label>
                            <input type="number" class="form-control product-quantity" id="quantity0" name="products[0][quantity]" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger remove-product">Xóa</button>
                        </div>
                    </div>`);
                productCount = 1;
                $('#totalAmount').val('');
                $('#submitBtn').text('Thêm đơn nhập kho');
            }

            // Form submission
            $('#orderForm').on('submit', function(e) {
                e.preventDefault();
                let action = $('#orderId').val() ? 'update' : 'add';
                let formData = new FormData(this);
                formData.append('action', action);
                $.ajax({
                    url: 'inbound_handler.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log('Form submission response:', response);
                        if (response.includes('success')) {
                            loadOrders(currentPage, lastSearchTerm);
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

            // Delete order
            $(document).on('click', '.delete-btn', function() {
                if (confirm('Bạn có chắc muốn xóa đơn nhập kho này?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: 'inbound_handler.php',
                        method: 'POST',
                        data: { action: 'delete', id: id },
                        success: function(response) {
                            console.log('Delete response:', response);
                            if (response === 'success') {
                                loadOrders(currentPage, lastSearchTerm);
                                alert('Xóa đơn nhập kho thành công!');
                            } else {
                                alert(response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Lỗi AJAX xóa:', status, error);
                            alert('Đã xảy ra lỗi khi xóa đơn nhập kho. Vui lòng thử lại.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
