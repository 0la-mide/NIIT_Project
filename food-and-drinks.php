<?php
require_once 'config.php';

// Get all active food items with their categories
$food_items = $conn->query("
    SELECT f.*, c.name as category_name, c.slug as category_slug 
    FROM food_items f
    JOIN food_categories c ON f.category_id = c.category_id
    WHERE f.is_active = 1
    ORDER BY c.name ASC, f.name ASC
");

// Get all categories for filtering
$categories = $conn->query("
    SELECT * FROM food_categories 
    ORDER BY name ASC
");

// Initialize an array to group items by category
$items_by_category = [];
while ($item = $food_items->fetch_assoc()) {
    $items_by_category[$item['category_name']][] = $item;
}

$page_title = "Food & Drinks | Anvora Cinemas";
require_once 'header.php';
?>

<main class="main-content">
    <section class="page-header">
        <h1>Cinema Delights</h1>
        <p>Enhance your movie experience with our delicious selection of snacks, meals, and beverages</p>
    </section>
    
    <div class="container">
        <div class="menu-categories">
            <button class="category-btn active" data-category="all">All Items</button>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <button class="category-btn" data-category="<?= htmlspecialchars($category['slug']) ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endwhile; ?>
        </div>
        
        <div class="menu-grid">
            <?php foreach ($items_by_category as $category_name => $items): ?>
                <?php foreach ($items as $item): ?>
                    <div class="menu-item" data-category="<?= htmlspecialchars($item['category_slug']) ?>">
                        <img src="uploads/food/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-item-img">
                        <div class="menu-item-details">
                            <div class="menu-item-title">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <span class="menu-item-price">₦<?= number_format($item['price'], 2) ?></span>
                            </div>
                            <p class="menu-item-desc"><?= htmlspecialchars($item['description']) ?></p>
                            <!--<button class="add-to-cart" data-id="<?= $item['item_id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>">
                                Add to Cart
                            </button>-->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php' ?>

<style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        background-color: #0a0a0a;
        color: white;
        padding-top: 80px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Header styles */
    .main-header {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        position: relative;
    }

    .logo img {
        height: 40px;
        width: auto;
    }

    .nav-container {
        display: flex;
        align-items: center;
        flex-grow: 1;
        justify-content: center;
    }

    .main-nav ul {
        display: flex;
        list-style: none;
    }

    .main-nav li {
        margin: 0 15px;
    }

    .main-nav a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        font-size: 16px;
        transition: color 0.3s;
        display: flex;
        align-items: center;
    }

    .main-nav a:hover {
        color: rgb(7, 140, 206);
    }

    .main-nav a i {
        margin-right: 8px;
        display: none;
    }

    .header-actions {
        display: flex;
        align-items: center;
    }

    .cart-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        margin-left: 20px;
        position: relative;
    }

    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: rgb(7, 140, 206);
        color: #000;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .mobile-menu-btn {
        display: none;
        background: transparent;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        z-index: 1001;
        padding: 10px;
    }

    .mobile-logo {
        display: none;
    }

    /* Main content styles */
    .main-content {
        padding-top: 40px;
        padding-bottom: 50px;
        min-height: 100vh;
    }

    .page-header {
        text-align: center;
        padding: 40px 20px;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/hero.jpg') center/cover;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        color: rgb(7, 140, 206);
    }

    .page-header p {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .menu-categories {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .category-btn {
        background: rgb(7, 140, 206);
        color: #000;
        border: none;
        padding: 10px 20px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
    }

    .category-btn.active, .category-btn:hover {
        background: #fff;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        padding: 0 20px;
    }

    .menu-item {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s;
    }

    .menu-item:hover {
        transform: translateY(-10px);
    }

    .menu-item-img {
        height: 200px;
        width: 100%;
        object-fit: cover;
    }

    .menu-item-details {
        padding: 20px;
    }

    .menu-item-title {
        font-size: 1.3rem;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
    }

    .menu-item-price {
        color: rgb(7, 140, 206);
        font-weight: bold;
    }

    .menu-item-desc {
        color: #ccc;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }

    .add-to-cart {
        background:rgb(7, 140, 206);
        color: #000;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        width: 100%;
        transition: background 0.3s;
    }

    .add-to-cart:hover {
        background: #fff;
    }

    .section-title {
        font-size: 2rem;
        margin-bottom: 30px;
        position: relative;
        display: inline-block;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: rgb(7, 140, 206);
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .nav-container {
            justify-content: flex-end;
        }
        
        .main-nav {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 80px 20px 20px;
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .mobile-logo {
            display: block;
            position: absolute;
            top: 15px;
            left: 20px;
        }
        
        .mobile-logo img {
            height: 30px;
        }
        
        .main-nav.active {
            left: 0;
        }
        
        .main-nav ul {
            flex-direction: column;
        }
        
        .main-nav li {
            margin: 15px 0;
        }
        
        .main-nav a i {
            display: inline-block;
            width: 20px;
            text-align: center;
        }
        
        .mobile-menu-btn {
            display: block !important;
            position: relative;
            order: 1;
        }
        
        .main-nav:not(.active) {
            display: none;
        }
        
        .nav-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 998;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        .nav-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .page-header h1 {
            font-size: 2rem;
        }
        
        .menu-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Scrolled state */
    .main-header.scrolled {
        background: rgba(0, 0, 0, 0.8);
        padding: 5px 0;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Food menu category filtering
        const categoryBtns = document.querySelectorAll('.category-btn');
        const menuItems = document.querySelectorAll('.menu-item');
        
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                menuItems.forEach(item => {
                    item.style.display = (category === 'all' || item.dataset.category === category) ? 'block' : 'none';
                });
            });
        });
        
        // Add to cart functionality
        const addToCartBtns = document.querySelectorAll('.add-to-cart');
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const countElement = document.querySelector('.cart-count');
                let count = parseInt(countElement.textContent);
                countElement.textContent = count + 1;
                
                // Animation
                countElement.style.transform = 'scale(1.5)';
                setTimeout(() => {
                    countElement.style.transform = 'scale(1)';
                }, 300);
                
                // Get item details
                const itemId = this.dataset.id;
                const itemName = this.dataset.name;
                const itemPrice = this.dataset.price;
                
                // Add to cart via AJAX
                fetch('add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}&name=${encodeURIComponent(itemName)}&price=${itemPrice}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error adding to cart:', data.message);
                        countElement.textContent = count; // Revert count if failed
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    countElement.textContent = count; // Revert count if failed
                });
            });
        });
    });
</script>