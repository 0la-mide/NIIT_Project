<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Enhanced viewport meta tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Anvora Cinemas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            min-height: 200vh; /* For demonstration */
        }
        
        /* Header styles */
        .main-header {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            position: relative; /* Added for z-index context */
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
            color:rgb(6, 89, 131);
        }
        
        .main-nav a i {
            margin-right: 8px;
            display: none; /* Hide icons by default (show only on mobile) */
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
            background: rgb(6, 89, 131);
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

        .mobile-logo{
            display: none;
        }
        
        /* Mobile styles - updated breakpoint and styles */
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
                display: inline-block; /* Show icons on mobile */
                width: 20px;
                text-align: center;
            }
            
            .mobile-menu-btn {
                display: block !important;
                position: relative;
                order: 1;
            }
            
            /* Hide desktop nav on mobile */
            .main-nav:not(.active) {
                display: none;
            }
            
            /* Overlay when menu is open */
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

            .mobile-logo{
                display: block;
            }
        }
        
        /* Scrolled state */
        .main-header.scrolled {
            background: rgba(0, 0, 0, 0.8);
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/logo.png" alt="Anvora Cinemas">
                    </a>
                </div>
                
                <div class="nav-container">
                    <nav class="main-nav">
                        <div class="mobile-logo">
                            <a href="index.php">
                                <img src="assets/logo.png" alt="Anvora Cinemas">
                            </a>
                        </div>
                        <ul>
                            <li><a href="food-and-drinks.php"><i class="fas fa-home"></i>Food & Drinks</a></li>
                            <li><a href="cinemas.php"><i class="fas fa-film"></i>Cinemas</a></li>
                            <li><a href="offers.php"><i class="fas fa-tag"></i>Offers</a></li>
                            <li><a href="about.php"><i class="fas fa-info-circle"></i>About</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="header-actions">
                    <!--<button class="cart-btn" aria-label="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </button>-->
                    <button class="mobile-menu-btn" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <div class="nav-overlay"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mainNav = document.querySelector('.main-nav');
            const navOverlay = document.querySelector('.nav-overlay');
            
            // Function to update menu state
            function updateMenuState() {
                if (window.innerWidth <= 768) {
                    // Mobile view
                    mobileMenuBtn.style.display = 'block';
                    if (!mainNav.classList.contains('active')) {
                        mainNav.style.display = 'none';
                    } else {
                        mainNav.style.display = 'block';
                    }
                } else {
                    // Desktop view
                    mobileMenuBtn.style.display = 'none';
                    mainNav.style.display = 'block';
                    mainNav.classList.remove('active');
                    navOverlay.classList.remove('active');
                    // Reset icon
                    const icon = mobileMenuBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
            
            // Initial check
            updateMenuState();
            
            // Handle menu toggle
            mobileMenuBtn.addEventListener('click', function() {
                mainNav.style.display = 'block';
                mainNav.classList.toggle('active');
                navOverlay.classList.toggle('active');
                
                // Change icon based on menu state
                const icon = this.querySelector('i');
                if (mainNav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close menu when clicking overlay
            navOverlay.addEventListener('click', function() {
                mainNav.classList.remove('active');
                this.classList.remove('active');
                mobileMenuBtn.querySelector('i').classList.remove('fa-times');
                mobileMenuBtn.querySelector('i').classList.add('fa-bars');
            });
            
            // Close mobile menu when clicking a link
            const navLinks = document.querySelectorAll('.main-nav a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        mainNav.classList.remove('active');
                        navOverlay.classList.remove('active');
                        mobileMenuBtn.querySelector('i').classList.remove('fa-times');
                        mobileMenuBtn.querySelector('i').classList.add('fa-bars');
                    }
                });
            });
            
            // Add scroll effect to header
            const header = document.querySelector('.main-header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                updateMenuState();
            });
            
            // Initialize header state
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            }
            
            // Cart button functionality (placeholder)
            const cartBtn = document.querySelector('.cart-btn');
            cartBtn.addEventListener('click', function() {
                alert('Cart functionality will be implemented here');
            });
        });
    </script>
</body>
</html>