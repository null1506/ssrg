<?php
session_start();
require_once "./functions/database_functions.php";
require_once "./auth.php";
checkLogin();

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$conn = db_connect();
$query = "SELECT * FROM users WHERE id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $query, $params);
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Lấy giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$total_price = 0;
$total_items = 0;

// Lấy thông tin sách trong giỏ hàng
$books = array();
foreach ($cart as $isbn => $qty) {
    $result = getBookByIsbn($conn, $isbn);
    $book = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    $book['quantity'] = $qty;
    $book['total'] = $qty * $book['book_price'];
    $books[] = $book;
    $total_price += $book['total'];
    $total_items += $qty;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn tổng hợp</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<div class="container" id="invoice">
    <h2>Hóa đơn tổng hợp</h2>
    <p>Khách hàng: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
    <p>Ngày: <?php echo date('Y-m-d H:i:s'); ?></p>
    <table class="table">
        <tr>
            <th>Sách</th>
            <th>Tác giả</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
        </tr>
        <?php foreach($books as $book): ?>
        <tr>
            <td><?php echo htmlspecialchars($book['book_title']); ?></td>
            <td><?php echo htmlspecialchars($book['book_author']); ?></td>
            <td><?php echo $book['quantity']; ?></td>
            <td><?php echo '$' . number_format($book['book_price'], 2); ?></td>
            <td><?php echo '$' . number_format($book['total'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th colspan="4" class="text-right">Tổng cộng</th>
            <th><?php echo '$' . number_format($total_price, 2); ?></th>
        </tr>
    </table>
    <!-- Nút chụp ảnh hóa đơn bằng trình duyệt -->
    <button id="capture-btn" class="btn btn-success">Chụp ảnh hóa đơn (trình duyệt)</button>
    <!-- Nút tải ảnh hóa đơn server-side (Node.js Puppeteer API) -->
    
    <form action="process.php" method="post" style="display:inline-block; margin-left:10px;">
        <button type="submit" class="btn btn-primary">Xác nhận đặt hàng</button>
    </form>
    <a href="cart.php" class="btn btn-default">Quay lại giỏ hàng</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('capture-btn').onclick = function() {
    html2canvas(document.getElementById('invoice')).then(function(canvas) {
        var link = document.createElement('a');
        link.download = 'review_invoice.png';
        link.href = canvas.toDataURL();
        link.click();
    });
};
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>