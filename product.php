<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$product_id = $_GET['id'] ?? 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get product details with seller info
    $stmt = $conn->prepare("
        SELECT p.*, u.username as seller_name 
        FROM products p
        JOIN users u ON p.seller_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header("Location: /c2c-platform/404.php");
        exit();
    }
    
    // Get product images (assuming you have a product_images table)
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // If no additional images, use main product image
    if (empty($product_images)) {
        $product_images = [$product['image']];
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

include 'views/header.php';
?>

<div class="product-page">
  <div class="product-gallery">
    <div class="main-image">
      <img src="/c2c-platform/assets/images/products/<?= $product_images[0] ?>" 
           alt="<?= htmlspecialchars($product['name']) ?>"
           id="main-product-image">
    </div>
    <div class="thumbnail-container">
      <?php foreach ($product_images as $index => $image): ?>
        <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
             data-image="<?= htmlspecialchars($image) ?>">
          <img src="/c2c-platform/assets/images/products/<?= htmlspecialchars($image) ?>" 
               alt="Thumbnail <?= $index + 1 ?>">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  
  <div class="product-details">
    <div class="product-header">
      <h1><?= htmlspecialchars($product['name']) ?></h1>
      <div class="product-meta">
        <span class="rating">
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star-half-alt"></i>
          <span>4.5 (24 reviews)</span>
        </span>
        <span class="stock-status in-stock">In Stock</span>
      </div>
    </div>
    
    <div class="price-section">
      <span class="current-price">R<?= number_format($product['price'], 2) ?></span>
      <?php if(isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
        <span class="original-price">R<?= number_format($product['original_price'], 2) ?></span>
        <span class="discount"><?= calculateDiscount($product['original_price'], $product['price']) ?>% OFF</span>
      <?php endif; ?>
    </div>
    
    <div class="product-description">
      <h3>Description</h3>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    </div>
    
    <div class="product-actions">
      <div class="quantity-selector">
        <button class="quantity-btn minus">-</button>
        <input type="number" value="1" min="1" class="quantity-input">
        <button class="quantity-btn plus">+</button>
      </div>
      <button class="btn btn-primary btn-add-to-cart">
        <i class="fas fa-cart-plus"></i> Add to Cart
      </button>
      <button class="btn btn-outline btn-wishlist">
        <i class="far fa-heart"></i> Add to Wishlist
      </button>
    </div>
    
    <div class="delivery-info">
      <div class="delivery-option">
        <i class="fas fa-truck"></i>
        <div>
          <strong>Free Delivery</strong>
          <span>For orders over R500</span>
        </div>
      </div>
      <div class="delivery-option">
        <i class="fas fa-undo"></i>
        <div>
          <strong>Easy Returns</strong>
          <span>14-day return policy</span>
        </div>
      </div>
    </div>
    
    <div class="product-seller-info">
      <h3>Seller Information</h3>
      <div class="seller-card">
        <div class="seller-avatar">
          <i class="fas fa-store"></i>
        </div>
        <div class="seller-details">
          <h4><?= htmlspecialchars($product['seller_name']) ?></h4>
          <div class="seller-rating">
            <i class="fas fa-star"></i> 4.8 (128 reviews)
          </div>
          <div class="seller-location">
            <i class="fas fa-map-marker-alt"></i> Johannesburg, South Africa
          </div>
          <button class="btn btn-outline btn-contact">Contact Seller</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Product Reviews Section -->
<section class="product-reviews">
  <h2>Product Reviews</h2>
  <div class="reviews-container">
    <div class="review">
      <div class="review-header">
        <div class="reviewer-avatar">T</div>
        <div class="reviewer-info">
          <h4>Thabo M.</h4>
          <div class="review-rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <span>2 days ago</span>
          </div>
        </div>
      </div>
      <div class="review-content">
        <p>Excellent product quality and fast delivery. Very happy with my purchase!</p>
      </div>
    </div>
    <!-- More reviews would be dynamically loaded -->
  </div>
  <button class="btn btn-outline view-all-reviews">View All Reviews</button>
</section>

<!-- Related Products Section -->
<section class="related-products">
  <h2>You May Also Like</h2>
  <div class="related-products-grid">
    <?php
    // This would be replaced with actual related products from database
    $sample_products = [
        ['id' => 1, 'name' => 'Wireless Earbuds', 'price' => 799.99, 'image' => 'earbuds.jpg'],
        ['id' => 2, 'name' => 'Smart Watch', 'price' => 1499.99, 'image' => 'watch.jpg'],
        ['id' => 3, 'name' => 'Bluetooth Speaker', 'price' => 599.99, 'image' => 'speaker.jpg']
    ];
    
    foreach ($sample_products as $related): ?>
    <div class="related-product-card">
      <a href="/c2c-platform/product.php?id=<?= $related['id'] ?>">
        <div class="related-product-image">
          <img src="/c2c-platform/assets/images/products/<?= $related['image'] ?>" 
               alt="<?= htmlspecialchars($related['name']) ?>">
        </div>
        <h3><?= htmlspecialchars($related['name']) ?></h3>
        <div class="related-product-price">R<?= number_format($related['price'], 2) ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'views/footer.php'; ?>

<script>
// Product image gallery functionality
document.querySelectorAll('.thumbnail').forEach(thumb => {
  thumb.addEventListener('click', function() {
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('main-product-image').src = 
      '/c2c-platform/assets/images/products/' + this.dataset.image;
  });
});

// Quantity selector
document.querySelector('.quantity-btn.minus').addEventListener('click', function() {
  const input = document.querySelector('.quantity-input');
  if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
});

document.querySelector('.quantity-btn.plus').addEventListener('click', function() {
  const input = document.querySelector('.quantity-input');
  input.value = parseInt(input.value) + 1;
});

// Add to cart functionality
document.querySelector('.btn-add-to-cart').addEventListener('click', function() {
  const productId = <?= $product['id'] ?>;
  const quantity = document.querySelector('.quantity-input').value;
  
  fetch('/c2c-platform/api/cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      action: 'add',
      product_id: productId,
      quantity: quantity
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Product added to cart!');
      // Update cart count in header
      if (document.getElementById('cart-count')) {
        document.getElementById('cart-count').textContent = data.cart_count;
      }
    } else {
      alert('Error: ' + data.message);
    }
  });
});
</script>