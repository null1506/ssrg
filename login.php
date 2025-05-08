<?php
session_start();
require_once "./functions/database_functions.php";
$conn = db_connect();

$title = "Login";
require "./template/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $error = [];

    if (empty($username) || empty($password)) {
        $error[] = "All fields are required";
    }

    if (empty($error)) {
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $params = array($username, $username);
        $stmt = sqlsrv_query($conn, $query, $params);
        
        if ($user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: index.php");
                exit();
            } else {
                $error[] = "Invalid password";
            }
        } else {
            $error[] = "User not found";
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($error as $err): ?>
                        <p><?php echo $err; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" class="needs-validation" novalidate>
                <div class="form-group mb-3">
                    <label for="username">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require "./template/footer.php";
sqlsrv_close($conn);
?> 