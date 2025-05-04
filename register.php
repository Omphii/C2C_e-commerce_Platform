<?php
require 'includes/config.php';
require 'includes/db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $user_type = $_POST['user_type'] ?? 'buyer';

    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (strlen($username) < 4) $errors[] = "Username must be at least 4 characters";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $password_confirm) $errors[] = "Passwords do not match";

    if (empty($errors)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username or email already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashed_password, $user_type])) {
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Registration error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/c2c-platform/assets/css/styles.css">
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container">
        <h1>Create Account</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <label>Confirm Password:</label>
            <input type="password" name="password_confirm" required>
            
            <label>Account Type:</label>
            <select name="user_type">
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
            </select>
            
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    
    <?php include 'views/footer.php'; ?>
</body>
</html>