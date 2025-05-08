<?php
session_start();
require_once "./functions/database_functions.php";
require_once "./auth.php";
checkLogin();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if (!$order_id) die("Order ID is required");

$conn = db_connect();

// Lấy thông tin đơn hàng
$query = "SELECT * FROM orders WHERE order_id = ?";
$params = array($order_id);
$stmt = sqlsrv_query($conn, $query, $params);
$order = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Lấy thông tin user
$query = "SELECT * FROM users WHERE id = ?";
$params = array($order['user_id']);
$stmt = sqlsrv_query($conn, $query, $params);
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Lấy danh sách sản phẩm trong đơn hàng
$query = "SELECT oi.*, b.book_title, b.book_author FROM order_items oi
          JOIN books b ON oi.book_isbn = b.book_isbn
          WHERE oi.order_id = ?";
$params = array($order_id);
$stmt = sqlsrv_query($conn, $query, $params);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container" id="invoice">
        <h2>Invoice #<?php echo $order_id; ?></h2>
        <p>User: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
        <p>Date: <?php echo $order['order_date']; ?></p>
        <table class="table">
            <tr>
                <th>Book</th>
                <th>Author</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            <?php while($item = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['book_title']); ?></td>
                <td><?php echo htmlspecialchars($item['book_author']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo '$' . number_format($item['price'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <h4>Total: $<?php echo number_format($order['total_amount'], 2); ?></h4>
        <button id="capture-btn" class="btn btn-success">Chụp ảnh hóa đơn</button>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    document.getElementById('capture-btn').onclick = function() {
        html2canvas(document.getElementById('invoice')).then(function(canvas) {
            var link = document.createElement('a');
            link.download = 'invoice_<?php echo $order_id; ?>.png';
            link.href = canvas.toDataURL();
            link.click();
        });
    };
    </script>
</body>
</html>

<?php
sqlsrv_close($conn);
?> 