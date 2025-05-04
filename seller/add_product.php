<?php
require '../includes/config.php';
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /c2c-platform/login.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category'] ?? '');
    
    // Validation
    if (empty($name)) $errors[] = "Product name is required";
    if (empty($price)) $errors[] = "Price is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            move_uploaded_file(
                $_FILES['image']['tmp_name'], 
                $_SERVER['DOCUMENT_ROOT'] . "/c2c-platform/assets/images/products/" . $image
            );
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    }
    
    if (empty($errors)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO products 
                (seller_id, name, description, price, image, category) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $_SESSION['user_id'],
                $name,
                $description,
                $price,
                $image,
                $category
            ])) {
                $_SESSION['success'] = "Product added successfully!";
                header("Location: products.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/c2c-platform/assets/css/styles.css">
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container">
        <h1>Add New Product</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="name" required>
            
            <label>Description:</label>
            <textarea name="description" rows="4"></textarea>
            
            <label>Price (R):</label>
            <input type="number" name="price" step="0.01" min="0" required>
            
            <label>Category:</label>
            <input type="text" name="category">
            
            <label>Product Image:</label>
            <input type="file" name="image" accept="image/*">
            
            <button type="submit">Add Product</button>
            <a href="products.php" class="btn">Cancel</a>
        </form>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>