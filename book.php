<?php
  session_start();
  $book_isbn = $_GET['bookisbn'];

  // Kết nối cơ sở dữ liệu SQL Server
  require_once "./functions/database_functions.php";
  $conn = db_connect(); // Hàm db_connect() trong file database_functions.php cần được viết để sử dụng sqlsrv_connect()
  //biến conn là biến kết nối đến sql server, được tạo từ sqlsrv_connect()

  // Sử dụng truy vấn có tham số để tránh SQL injection
  $query = "SELECT * FROM books WHERE book_isbn = '$book_isbn'";
  $result = sqlsrv_query($conn, $query);
  if(!$result){
    echo "Can't retrieve data: ";
    die(print_r(sqlsrv_errors(), true));
  }

  $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
  if(!$row){
    echo "Empty book";
    exit;
  }

  $title = $row['book_title'];

/*
  // Sử dụng truy vấn có tham số để tránh SQL injection
$query = "SELECT * FROM books WHERE book_isbn = ?";

// Chuẩn bị câu lệnh SQL
$params = array($book_isbn); //Khai báo 1 mảng chứa biến $book_isbn, nếu có 2 biến thì cho cả 2 biến vào mảng
$stmt = sqlsrv_prepare($conn, $query, $params);

if(!$stmt) {
    echo "Query preparation failed: ";
    die(print_r(sqlsrv_errors(), true));
}

// Thực thi truy vấn
if(!sqlsrv_execute($stmt)) {
    echo "Can't retrieve data: ";
    die(print_r(sqlsrv_errors(), true));
}

// Lấy kết quả
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if(!$row) {
    echo "Empty book";
    exit;
}

$title = $row['book_title'];
?>
*/


  require "./template/header.php";
?>
      <!-- Example row of columns -->
      <p class="lead" style="margin: 25px 0"><a href="books.php">Books</a> > <?php echo $row['book_title']; ?></p>
      <div class="row">
        <div class="col-md-3 text-center">
          <img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo $row['book_image']; ?>">
        </div>
        <div class="col-md-6">
          <h4>Book Description</h4>
          <p><?php echo $row['book_descr']; ?></p>
          <h4>Book Details</h4>
          <table class="table">
          	<?php 
            
              // Duyệt mảng dữ liệu trả về
              
              foreach($row as $key => $value){
                if($key === "book_descr" || $key === "book_image" || $key === "publisherid" || $key === "book_title"){
                  continue;
                }
                
                switch($key){
                  case "book_isbn":
                    $key = "ISBN";
                    break;
                  case "book_author":
                    $key = "Author";
                    break;
                  case "book_price":
                    $key= "Price";
                    break;
                }
                    
            ?>
            
            <tr>
              <td><?php echo $key; ?></td>
              <td><?php echo $value; ?></td>
            </tr>
            <?php 
              } 
            
              // Đóng kết nối SQL Server
              if(isset($conn)) { sqlsrv_close($conn); }
            ?>
          </table>
          <form method="post" action="cart.php">
            <input type="hidden" name="bookisbn" value="<?php echo $book_isbn; ?>">
            <input type="submit" value="Purchase / Add to cart" name="cart" class="btn btn-primary">
          </form>
       	</div>
      </div>
<?php
  require "./template/footer.php";
?>
