<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

// Process filters
$filters = [
    'min_price' => $_GET['min_price'] ?? 0,
    'max_price' => $_GET['max_price'] ?? 10000,
    'categories' => $_GET['categories'] ?? [],
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest'
];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Base query
    $query = "SELECT p.*, u.username as seller_name FROM products p 
              JOIN users u ON p.seller_id = u.id 
              WHERE p.price BETWEEN :min_price AND :max_price";
    
    // Add category filter if selected
    if (!empty($filters['categories'])) {
        $query .= " AND p.category IN (" . 
                  implode(',', array_fill(0, count($filters['categories']), '?')) . ")";
    }
    
    // Add search filter
    if (!empty($filters['search'])) {
        $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    }
    
    // Add sorting
    switch ($filters['sort']) {
        case 'price_low':
            $query .= " ORDER BY p.price ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY p.price DESC";
            break;
        case 'popular':
            $query .= " ORDER BY (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) DESC";
            break;
        default: // newest
            $query .= " ORDER BY p.created_at DESC";
    }
    
    // Prepare and execute
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bindValue(':min_price', $filters['min_price']);
    $stmt->bindValue(':max_price', $filters['max_price']);
    
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $stmt->bindValue(':search', $searchTerm);
    }
    
    // Execute with categories if needed
    if (!empty($filters['categories'])) {
        $stmt->execute($filters['categories']);
    } else {
        $stmt->execute();
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all categories for filter
    $stmt = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

include 'views/header.php';
?>

<div class="products-page">
  <aside class="filter-sidebar">
    <div class="filter-header">
      <h3>Filter Products</h3>
      <button class="btn btn-link reset-filters">Reset</button>
    </div>
    
    <form id="filter-form" method="get">
      <div class="filter-section">
        <h4>Price Range</h4>
        <div class="price-range">
          <div class="price-inputs">
            <input type="number" name="min_price" placeholder="Min" value="<?= $filters['min_price'] ?>">
            <span>-</span>
            <input type="number" name="max_price" placeholder="Max" value="<?= $filters['max_price'] ?>">
          </div>
          <input type="range" min="0" max="10000" value="<?= $filters['max_price'] ?>" 
                 class="slider" id="price-range">
        </div>
      </div>
      
      <div class="filter-section">
        <h4>Categories</h4>
        <ul class="category-list">
          <?php foreach ($categories as $category): ?>
            <li>
              <label>
                <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category) ?>"
                  <?= in_array($category, (array)$filters['categories']) ? 'checked' : '' ?>>
                <?= htmlspecialchars($category) ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      
      <div class="filter-section">
        <h4>Sort By</h4>
        <select name="sort" class="sort-select">
          <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
          <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Most Popular</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-primary apply-filters">Apply Filters</button>
    </form>
  </aside>
  
  <main class="product-listings">
    <div class="listing-header">
      <h2>All Products</h2>
      <div class="search-box">
        <form method="get">
          <input type="text" name="search" placeholder="Search products..." 
                 value="<?= htmlspecialchars($filters['search']) ?>">
          <button type="submit"><i class="fas fa-search"></i></button>
        </form>
      </div>
    </div>
    
    <?php if (empty($products)): ?>
      <div class="no-results">
        <i class="fas fa-search"></i>
        <h3>No products found</h3>
        <p>Try adjusting your filters or search term</p>
        <a href="products.php" class="btn btn-outline">Reset Filters</a>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <div class="product-card">
            <?php if ($product['featured']): ?>
              <span class="product-badge featured-badge">Featured</span>
            <?php endif; ?>
            
            <div class="product-image-container">
              <img src="/c2c-platform/assets/images/products/<?= $product['image'] ?? 'placeholder.jpg' ?>" 
                   alt="<?= htmlspecialchars($product['name']) ?>"
                   onerror="this.src='/c2c-platform/assets/images/placeholder.jpg'">
            </div>
            
            <div class="product-info">
              <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
              <div class="product-price">R<?= number_format($product['price'], 2) ?></div>
              <div class="product-seller">
                <i class="fas fa-store"></i> <?= htmlspecialchars($product['seller_name']) ?>
              </div>
              <div class="product-actions">
                <a href="/c2c-platform/product.php?id=<?= $product['id'] ?>" class="btn btn-primary">View</a>
                <button class="btn btn-outline quick-add" data-id="<?= $product['id'] ?>">
                  <i class="fas fa-cart-plus"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <div class="pagination">
        <a href="#" class="page-link active">1</a>
        <a href="#" class="page-link">2</a>
        <a href="#" class="page-link">3</a>
        <span class="page-dots">...</span>
        <a href="#" class="page-link">10</a>
        <a href="#" class="page-link next">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>
// Price range slider
const priceSlider = document.getElementById('price-range');
const maxPriceInput = document.querySelector('input[name="max_price"]');

if (priceSlider && maxPriceInput) {
  priceSlider.addEventListener('input', function() {
    maxPriceInput.value = this.value;
  });
  
  maxPriceInput.addEventListener('change', function() {
    priceSlider.value = this.value;
  });
}

// Quick add to cart
document.querySelectorAll('.quick-add').forEach(btn => {
  btn.addEventListener('click', function() {
    const productId = this.dataset.id;
    
    fetch('/c2c-platform/api/cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'add',
        product_id: productId,
        quantity: 1
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success notification
        const notification = document.createElement('div');
        notification.className = 'notification success';
        notification.innerHTML = `
          <i class="fas fa-check-circle"></i>
          <span>Product added to cart!</span>
        `;
        document.body.appendChild(notification);
        
        // Update cart count
        if (document.getElementById('cart-count')) {
          document.getElementById('cart-count').textContent = data.cart_count;
        }
        
        // Remove notification after 3 seconds
        setTimeout(() => {
          notification.remove();
        }, 3000);
      }
    });
  });
});

// Reset filters
document.querySelector('.reset-filters').addEventListener('click', function() {
  window.location.href = 'products.php';
});
</script>

<?php include 'views/footer.php'; ?>