<?php	
	session_start();

	$_SESSION['err'] = 1;
	foreach($_POST as $key => $value){
		if(trim($value) == ''){
			$_SESSION['err'] = 0;
		}
		break;
	}

	if($_SESSION['err'] == 0){
		header("Location: purchase.php");
		exit;
	} else {
		unset($_SESSION['err']);
	}

	require_once "./functions/database_functions.php";
	// In header
	$title = "Purchase Process";
	require "./template/header.php";

	// Kết nối CSDL SQL Server
	$conn = db_connect();
	// Giả sử $_SESSION['ship'] chứa các thông tin ship: name, address, city, zip_code, country
	extract($_SESSION['ship']);

	// Validate phần dữ liệu thẻ
	$card_number = $_POST['card_number'];
	$card_PID = $_POST['card_PID'];
	$card_expire = strtotime($_POST['card_expire']);
	$card_owner = $_POST['card_owner'];

	// Tìm customer theo thông tin giao hàng
	$customerid = getCustomerId($name, $address, $city, $zip_code, $country);
	if($customerid == null) {
		// Nếu không có, insert customer và lấy customerid
		$customerid = setCustomerId($name, $address, $city, $zip_code, $country);
	}

	$date = date("Y-m-d H:i:s");
	// Chèn đơn hàng vào bảng orders
	$query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
	$params = array($_SESSION['user_id'], $_SESSION['total_price']);
	$stmt = sqlsrv_query($conn, $query, $params);
	if ($stmt === false) {
		echo "Insert orders failed: ", print_r(sqlsrv_errors(), true);
		exit;
	}

	// Lấy order_id vừa tạo
	$query = "SELECT SCOPE_IDENTITY() as order_id";
	$stmt = sqlsrv_query($conn, $query);
	$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
	$orderid = $row['order_id'];

	// Kiểm tra orderid
	if (!$orderid) {
		echo "Không lấy được order_id. Thông tin debug: ";
		var_dump($row);
		exit;
	}

	// Chèn từng sản phẩm vào order_items
	foreach ($_SESSION['cart'] as $isbn => $qty) {
		$bookprice = getbookprice($isbn);
		$query = "INSERT INTO order_items (order_id, book_isbn, quantity, price) VALUES (?, ?, ?, ?)";
		$params = array($orderid, $isbn, $qty, $bookprice);
		$result = sqlsrv_query($conn, $query, $params);
		if ($result === false) {
			echo "Insert order_items failed: ", print_r(sqlsrv_errors(), true);
			exit;
		}
	}

	// Lưu order_id vào session để sử dụng cho việc chụp ảnh
	$_SESSION['last_order_id'] = $orderid;
?>
	<p class="lead text-success">
		Your order has been processed successfully. Please check your email to get your order confirmation and shipping detail!. 
		Your cart has been emptied.
	</p>

	<div class="text-center">
		<a href="index.php" class="btn btn-primary">Back to Home</a>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
	<script>
		// Tạo iframe ẩn để tải trang hóa đơn
		var iframe = document.createElement('iframe');
		iframe.style.display = 'none';
		iframe.src = 'invoice.php?order_id=<?php echo $orderid; ?>';
		document.body.appendChild(iframe);

		// Khi iframe tải xong, chụp ảnh và tải xuống
		iframe.onload = function() {
			html2canvas(iframe.contentDocument.getElementById('invoice')).then(function(canvas) {
				// Tạo link tải ảnh
				var link = document.createElement('a');
				link.download = 'invoice_<?php echo $orderid; ?>.png';
				link.href = canvas.toDataURL();
				link.click();
			});
		};
	</script>

<?php
	if(isset($conn)){
		sqlsrv_close($conn);
	}
	require_once "./template/footer.php";
?>
