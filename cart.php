<?php
session_start();
require_once 'config.php';

$page_title = "Your Cart | Anvora Cinemas";
require_once 'header.php';
?>

<br><br><br><br>
<main class="main-content">
    <section class="page-header">
        <h1>Your Cart</h1>
        <p>Review your selected items before checkout</p>
    </section>
    
    <div class="container">
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items yet</p>
                <a href="food-and-drinks.php" class="btn btn-primary">Browse</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <div class="cart-header">
                    <div class="cart-column">Item</div>
                    <div class="cart-column">Price</div>
                    <div class="cart-column">Quantity</div>
                    <div class="cart-column">Total</div>
                    <div class="cart-column">Action</div>
                </div>
                
                <?php 
                $subtotal = 0;
                foreach($_SESSION['cart'] as $id => $item): 
                    $item_total = $item['price'] * $item['quantity'];
                    $subtotal += $item_total;
                ?>
                    <div class="cart-item" data-id="<?= $id ?>">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                        </div>
                        <div class="cart-item-price">$<?= number_format($item['price'], 2) ?></div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus" data-id="<?= $id ?>">-</button>
                            <span class="quantity"><?= $item['quantity'] ?></span>
                            <button class="quantity-btn plus" data-id="<?= $id ?>">+</button>
                        </div>
                        <div class="cart-item-total">$<?= number_format($item_total, 2) ?></div>
                        <div class="cart-item-action">
                            <button class="remove-btn" data-id="<?= $id ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%):</span>
                        <span>$<?= number_format($subtotal * 0.1, 2) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?= number_format($subtotal * 1.1, 2) ?></span>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="food-and-drinks.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button class="btn btn-primary checkout-btn">
                            Proceed to Checkout <i class="fas fa-credit-card"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Cart Page Styles */
    .empty-cart {
        text-align: center;
        padding: 50px 20px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        margin: 30px 0;
    }
    
    .empty-cart i {
        font-size: 3rem;
        color: rgb(7, 140, 206);
        margin-bottom: 20px;
    }
    
    .empty-cart h2 {
        margin-bottom: 10px;
    }
    
    .empty-cart p {
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .cart-items {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .cart-header {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr;
        padding: 15px 20px;
        background: rgba(255,255,255,0.1);
        font-weight: bold;
    }
    
    .cart-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .cart-item-details h3 {
        margin-bottom: 5px;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: rgb(7, 140, 206);
        color: #000;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quantity-btn:hover {
        background: #fff;
    }
    
    .remove-btn {
        background: transparent;
        border: none;
        color: #ff6b6b;
        cursor: pointer;
        font-size: 1.1rem;
    }
    
    .cart-summary {
        background: rgba(255,255,255,0.05);
        padding: 20px;
        border-radius: 10px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .summary-row.total {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid rgba(255,255,255,0.2);
    }
    
    .cart-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s;
    }
    
    .btn i {
        margin-right: 8px;
    }
    
    .btn-primary {
        background: rgb(7, 140, 206);
        color: #000;
        border: none;
    }
    
    .btn-primary:hover {
        background: #fff;
    }
    
    .btn-outline {
        background: transparent;
        border: 1px solid rgb(7, 140, 206);
        color:rgb(7, 140, 206);
    }
    
    .btn-outline:hover {
        background: rgba(245, 166, 35, 0.1);
    }
    
    @media (max-width: 768px) {
        .cart-header {
            display: none;
        }
        
        .cart-item {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 15px;
        }
        
        .cart-item > div {
            display: flex;
            justify-content: space-between;
        }
        
        .cart-item::before {
            content: attr(data-label);
            font-weight: bold;
        }
        
        .cart-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            justify-content: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity adjustment
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const isPlus = this.classList.contains('plus');
                const quantityElement = this.parentElement.querySelector('.quantity');
                let quantity = parseInt(quantityElement.textContent);
                
                if (isPlus) {
                    quantity++;
                } else {
                    if (quantity > 1) quantity--;
                }
                
                // Update via AJAX
                updateCartItem(id, quantity);
            });
        });
        
        // Remove item
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (confirm('Are you sure you want to remove this item?')) {
                    updateCartItem(id, 0); // 0 quantity removes the item
                }
            });
        });
        
        // Checkout button
        document.querySelector('.checkout-btn')?.addEventListener('click', function() {
            alert('Checkout functionality will be implemented here');
            // In a real implementation, this would redirect to a checkout page
        });
        
        function updateCartItem(id, quantity) {
            fetch('update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${id}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (quantity === 0) {
                        // Remove item from DOM
                        document.querySelector(`.cart-item[data-id="${id}"]`)?.remove();
                        
                        // If cart is now empty, reload page to show empty cart message
                        if (data.cart_count === 0) {
                            location.reload();
                        }
                    } else {
                        // Update quantity display
                        const quantityElement = document.querySelector(`.cart-item[data-id="${id}"] .quantity`);
                        if (quantityElement) quantityElement.textContent = quantity;
                        
                        // Update item total
                        const price = parseFloat(document.querySelector(`.cart-item[data-id="${id}"] .cart-item-price`).textContent.replace('$', ''));
                        document.querySelector(`.cart-item[data-id="${id}"] .cart-item-total`).textContent = '$' + (price * quantity).toFixed(2);
                    }
                    
                    // Update cart count in header
                    document.querySelector('.cart-count').textContent = data.cart_count;
                    
                    // Update summary totals
                    if (data.subtotal !== undefined) {
                        document.querySelectorAll('.summary-row span')[1].textContent = '$' + data.subtotal.toFixed(2);
                        document.querySelectorAll('.summary-row span')[3].textContent = '$' + (data.subtotal * 0.1).toFixed(2);
                        document.querySelectorAll('.summary-row span')[5].textContent = '$' + (data.subtotal * 1.1).toFixed(2);
                    }
                } else {
                    alert('Error updating cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating your cart');
            });
        }
    });
</script>

<?php require_once 'footer.php' ?>