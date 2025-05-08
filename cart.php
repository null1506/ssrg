<?php
    session_start();
    require_once "./functions/database_functions.php";
    require_once "./functions/cart_functions.php";
    require_once "./auth.php";
    checkLogin();

    $conn = db_connect();
    $user_id = $_SESSION['user_id'];

    // Nếu có book_isbn được gửi qua POST, thêm vào giỏ hàng
    if(isset($_POST['bookisbn'])){
        $book_isbn = $_POST['bookisbn'];
    }
    if(isset($book_isbn)){
        if(!isset($_SESSION['cart'])){
            // Khởi tạo giỏ hàng dưới dạng mảng: bookisbn => số lượng
            $_SESSION['cart'] = array();
            $_SESSION['total_items'] = 0;
            $_SESSION['total_price'] = '0.00';
        }

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $query = "SELECT quantity FROM cart_items WHERE user_id = ? AND book_isbn = ?";
        $params = array($user_id, $book_isbn);
        $stmt = sqlsrv_query($conn, $query, $params);
        
        if(sqlsrv_fetch($stmt)) {
            // Nếu sản phẩm đã có, cập nhật số lượng
            $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND book_isbn = ?";
            $params = array($user_id, $book_isbn);
            sqlsrv_query($conn, $query, $params);
        } else {
            // Nếu sản phẩm chưa có, thêm mới
            $query = "INSERT INTO cart_items (user_id, book_isbn, quantity) VALUES (?, ?, 1)";
            $params = array($user_id, $book_isbn);
            sqlsrv_query($conn, $query, $params);
        }

        // Cập nhật session cart
        if(!isset($_SESSION['cart'][$book_isbn])){
            $_SESSION['cart'][$book_isbn] = 1;
        } elseif(isset($_POST['cart'])){
            $_SESSION['cart'][$book_isbn]++;
            unset($_POST);
        }
    }

    // Nếu người dùng bấm nút "Save Changes", cập nhật số lượng cho từng sản phẩm
    if(isset($_POST['save_change'])){
        foreach($_SESSION['cart'] as $isbn => $qty){
            if($_POST[$isbn] == '0'){
                // Xóa sản phẩm khỏi giỏ hàng trong database
                $query = "DELETE FROM cart_items WHERE user_id = ? AND book_isbn = ?";
                $params = array($user_id, $isbn);
                sqlsrv_query($conn, $query, $params);
                unset($_SESSION['cart'][$isbn]);
            } else {
                // Cập nhật số lượng trong database
                $query = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND book_isbn = ?";
                $params = array($_POST[$isbn], $user_id, $isbn);
                sqlsrv_query($conn, $query, $params);
                $_SESSION['cart'][$isbn] = $_POST[$isbn];
            }
        }
    }

    // Lấy giỏ hàng từ database nếu session cart trống
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $query = "SELECT book_isbn, quantity FROM cart_items WHERE user_id = ?";
        $params = array($user_id);
        $stmt = sqlsrv_query($conn, $query, $params);
        
        $_SESSION['cart'] = array();
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $_SESSION['cart'][$row['book_isbn']] = $row['quantity'];
        }
    }

    $title = "Your shopping cart";
    require "./template/header.php";

    if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0){
        $_SESSION['total_price'] = total_price($_SESSION['cart']);
        $_SESSION['total_items'] = total_items($_SESSION['cart']);
?>
    <form action="cart.php" method="post">
        <div id="invoice">
        <table class="table">
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php
                // Lặp qua từng sản phẩm trong giỏ hàng
                foreach($_SESSION['cart'] as $isbn => $qty){
                    // Hàm getBookByIsbn() trong database_functions.php nên được chuyển sang dùng sqlsrv_query()
                    $result = getBookByIsbn($conn, $isbn);
                    $book = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            ?>
            <tr>
                <td><?php echo $book['book_title'] . " by " . $book['book_author']; ?></td>
                <td><?php echo "$" . $book['book_price']; ?></td>
                <td><input type="text" value="<?php echo $qty; ?>" size="2" name="<?php echo $isbn; ?>"></td>
                <td><?php echo "$" . ($qty * $book['book_price']); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th><?php echo $_SESSION['total_items']; ?></th>
                <th><?php echo "$" . $_SESSION['total_price']; ?></th>
            </tr>
        </table>
        </div>
        <input type="submit" class="btn btn-primary" name="save_change" value="Save Changes">
    </form>
    <br/><br/>
    <a href="review_invoice.php" class="btn btn-primary">Go To Checkout</a> 
    <a href="books.php" class="btn btn-primary">Continue Shopping</a>
    <a href="http://localhost.com/online-book-store-project-in-php/tool/screenshot/screenshot.php?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' .'localhost'. '/online-book-store-project-in-php/review_invoice.php'); ?>" class="btn btn-info" download="invoice_server.png">Tải ảnh hóa đơn server-side (Node.js)</a>
<?php
    } else {
        echo "<p class=\"text-warning\">Your cart is empty! Please make sure you add some books in it!</p>";
    }
    require_once "./template/footer.php";
    sqlsrv_close($conn);
?>
