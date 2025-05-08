<?php
session_start();
require_once "./functions/database_functions.php";
$conn = db_connect();

$title = "Register";
require "./template/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $error = [];

    // Validate username
    if (empty($username)) {
        $error[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $error[] = "Username must be at least 3 characters";
    }

    // Validate email
    if (empty($email)) {
        $error[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Invalid email format";
    }

    // Validate password
    if (empty($password)) {
        $error[] = "Password is required";
    } 

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error[] = "Passwords do not match";
    }

    // Check if username or email already exists
    if (empty($error)) {
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $params = array($username, $email);
        $stmt = sqlsrv_query($conn, $query, $params);
        
        if (sqlsrv_fetch($stmt)) {
            $error[] = "Username or email already exists";
        } else {
            // Hash password and insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
            $params = array($username, $email, $hashed_password);
            $stmt = sqlsrv_query($conn, $query, $params);

            if ($stmt) {
                $_SESSION['message'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Register</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($error as $err): ?>
                        <p><?php echo $err; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="register.php" class="needs-validation" novalidate>
                <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="form-group mb-3">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require "./template/footer.php";
sqlsrv_close($conn);
?> 