<div class="container">
    <h2>Featured Products</h2>
    
    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach($products as $product): ?>
                <div class="product-card">
                    <div class="product-image-container">
                    <div class="product-image-container">
    <img 
        src="/c2c-platform/assets/images/products/<?= htmlspecialchars($product['image'] ?? 'placeholder.jpg') ?>" 
        alt="<?= htmlspecialchars($product['name']) ?>"
        onerror="this.onerror=null;this.src='/c2c-platform/assets/images/placeholder.jpg'"
    >
</div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price">R<?= number_format($product['price'], 2) ?></div>
                        <div class="seller">
                            Sold by: <?= htmlspecialchars($product['seller_name'] ?? 'Unknown Seller') ?>
                        </div>
                        <div class="actions">
                            <a href="/product.php?id=<?= $product['id'] ?>" class="btn view-btn">View</a>
                            <button class="btn cart-btn" data-id="<?= $product['id'] ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-products">No featured products available at this time.</p>
        <?php endif; ?>
    </div>
</div>