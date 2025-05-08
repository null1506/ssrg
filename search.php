<?php
require "./template/header.php";
session_start();
require_once './functions/database_functions.php'; // Chứa hàm db_connect() sử dụng sqlsrv_connect()
$conn = db_connect();

$price = '';
$bookCount = 0;
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy giá trị từ form POST
    $price = trim($_POST['price']);
    if ($price != '') {
        // Sử dụng truy vấn có tham số để đếm số sản phẩm có giá nhỏ hơn hoặc bằng mức giá nhập
        $query = "SELECT COUNT(book_isbn) AS BookCount FROM books WHERE book_price <= '$price'";
        //$params = array($price);
        //$stmt = sqlsrv_query($conn, $query,$params, array("Scrollable" => SQLSRV_CURSOR_STATIC));
        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            //die(print_r(sqlsrv_errors(), true));

            die(print_r("fail", true));
        }
        // Lấy giá trị đếm được
        if (sqlsrv_fetch($stmt) !== false) {
            $bookCount = sqlsrv_get_field($stmt, 0);
        }
    }
}
*/
//$responseMessage = "Operation completed successfully.";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lấy giá trị từ form POST mà không thực hiện kiểm tra kiểu số để cho phép payload injection
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';

        // Xây dựng truy vấn bằng cách nối chuỗi trực tiếp (vulnerable to SQL injection)
        // Nếu người dùng nhập payload "20'; WAITFOR DELAY '0:0:10' -- -", câu lệnh sẽ thành:
        // SELECT COUNT(book_isbn) AS BookCount FROM books WHERE book_price <= '20'; WAITFOR DELAY '0:0:10'
        $query = "SELECT COUNT(book_isbn) AS BookCount FROM books WHERE book_price <= '$price' ";

        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            // Nếu truy vấn thất bại (ví dụ do cấu hình không cho phép multiple statements), ném exception
            die(print_r("fail", true));
            /*
            throw new Exception("fail");
            $responseMessage = "fail";*/
        }

        // Lấy giá trị đếm được
        
        //$bookCount = sqlsrv_get_field($stmt, 0);
        if (sqlsrv_fetch($stmt)) {
            $bookCount = sqlsrv_get_field($stmt, 0);
        }
        
    }
} catch (Exception $e) {
    // Ghi log lỗi nội bộ, nhưng trả về thông báo chung OK cho client
    error_log($e->getMessage());
    $responseMessage = "fail";
}


#kệ đoạn dưới đây
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy giá trị từ form POST
    $price = trim($_POST['price']);
    if ($price !== '' && is_numeric($price)) {
        // Sử dụng truy vấn có tham số để đếm số sản phẩm có giá nhỏ hơn hoặc bằng mức giá nhập
        $query = "SELECT COUNT(book_isbn) AS BookCount FROM books WHERE book_price <= '$price'";
        //$params = array($price);
        //$stmt = sqlsrv_query($conn, $query,$params, array("Scrollable" => SQLSRV_CURSOR_STATIC));
        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        // Lấy giá trị đếm được
        if (sqlsrv_fetch($stmt) !== false) {
            $bookCount = sqlsrv_get_field($stmt, 0);
        }
    }
}
 */
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Books Search by Price</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1>Search Books by Price</h1>
        <!-- Form tra cứu sản phẩm theo giá sử dụng phương thức POST -->
        <form method="post" action="search.php" class="form-inline">
            <div class="form-group">
                <label for="price">Enter price (find books with price less than input):</label>
                <input type="text" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <hr>
        <?php if ($price !== ''): ?>
            <h3>There are <?php echo $bookCount; ?> books with price less than <?php echo htmlspecialchars($price); ?>.</h3>
        <?php else: ?>
            <p>Please enter a price to search for books.</p>
        <?php endif; ?>
    </div>
</body>

</html>
<?php
sqlsrv_close($conn);
?>



