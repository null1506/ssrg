<?php
session_start();
require_once "./functions/database_functions.php";

// Nếu người dùng đã đăng nhập và có sản phẩm trong giỏ hàng
if (isset($_SESSION['user_id']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $conn = db_connect();
    
    try {
        // Bắt đầu transaction
        sqlsrv_begin_transaction($conn);
        
        // Tạo đơn hàng mới
        $query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
        $params = array($_SESSION['user_id'], $_SESSION['total_price']);
        $stmt = sqlsrv_query($conn, $query, $params);
        
        if ($stmt === false) {
            throw new Exception("Error creating order");
        }
        
        // Lấy ID của đơn hàng vừa tạo
        $query = "SELECT SCOPE_IDENTITY() as order_id";
        $stmt = sqlsrv_query($conn, $query);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $order_id = $row['order_id'];
        
        // Lưu từng sản phẩm vào order_items
        foreach ($_SESSION['cart'] as $isbn => $qty) {
            // Lấy giá sách
            $query = "SELECT book_price FROM books WHERE book_isbn = ?";
            $params = array($isbn);
            $stmt = sqlsrv_query($conn, $query, $params);
            $book = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            // Thêm vào order_items
            $query = "INSERT INTO order_items (order_id, book_isbn, quantity, price) VALUES (?, ?, ?, ?)";
            $params = array($order_id, $isbn, $qty, $book['book_price']);
            $stmt = sqlsrv_query($conn, $query, $params);
            
            if ($stmt === false) {
                throw new Exception("Error adding order items");
            }
        }
        
        // Commit transaction
        sqlsrv_commit($conn);
        
        // Lưu order_id vào session để có thể truy cập sau này
        $_SESSION['last_order_id'] = $order_id;
        
    } catch (Exception $e) {
        // Nếu có lỗi, rollback transaction
        sqlsrv_rollback($conn);
        error_log("Error saving cart: " . $e->getMessage());
    }
    
    sqlsrv_close($conn);
}

// Xóa session và chuyển hướng về trang login
session_destroy();
header("Location: login.php");
exit();
?> 