<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Special Offers | Anvora Cinemas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
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
        }
        
        /* Scrolled state */
        .main-header.scrolled {
            background: rgba(0, 0, 0, 0.8);
            padding: 5px 0;
        }
        
        /* Main content styles */
        .main-content {
            padding-top: 100px;
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
        
        .offers-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .offer-card {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            display: flex;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }
        
        .offer-image {
            flex: 0 0 40%;
            min-height: 250px;
            background-size: cover;
            background-position: center;
            background-color: #333; /* Fallback color */
        }
        
        .offer-content {
            flex: 1;
            padding: 30px;
            position: relative;
        }
        
        .offer-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgb(7, 140, 206);
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .offer-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: rgb(7, 140, 206);
        }
        
        .offer-description {
            margin-bottom: 20px;
            line-height: 1.6;
            color: #ccc;
        }
        
        .offer-details {
            margin-bottom: 20px;
        }
        
        .offer-details p {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .offer-details i {
            margin-right: 10px;
            color: rgb(7, 140, 206);
            width: 20px;
            text-align: center;
        }
        
        .claim-btn {
            background: rgb(6, 89, 131);
            color: #000;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .claim-btn:hover {
            background: #fff;
            transform: translateY(-2px);
        }
        
        .claim-btn i {
            margin-left: 8px;
            transition: transform 0.3s;
        }
        
        .claim-btn:hover i {
            transform: translateX(3px);
        }
        
        .section-title {
            font-size: 2rem;
            margin: 50px 0 30px;
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100px;
            height: 3px;
            background: rgb(7, 140, 206);
        }
        
        @media (max-width: 768px) {
            .offer-card {
                flex-direction: column;
            }
            
            .offer-image {
                flex: 0 0 200px;
                width: 100%;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .offer-title {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1.7rem;
                margin: 30px 0 20px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'header.php';?>
    
    <!-- Main Content -->
    <main class="main-content">
        <section class="page-header">
            <h1>Special Offers</h1>
            <p>Enjoy these exclusive deals and save on your next cinema experience</p>
        </section>
        
        <div class="offers-container">
            <h2 class="section-title">Current Promotions</h2>
            
            <div class="offer-card">
                <div class="offer-image" style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80')"></div>
                <div class="offer-content">
                    <span class="offer-badge">Limited Time</span>
                    <h3 class="offer-title">Tuesday Discount Day</h3>
                    <p class="offer-description">
                        Enjoy 30% off on all movie tickets every Tuesday. Perfect for a mid-week escape to the cinema!
                    </p>
                    <div class="offer-details">
                        <p><i class="fas fa-calendar-alt"></i> Valid every Tuesday</p>
                        <p><i class="fas fa-ticket-alt"></i> All movies included</p>
                        <p><i class="fas fa-user-friends"></i> Up to 4 tickets per transaction</p>
                    </div>
                    <button class="claim-btn">Claim Offer <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            
            <div class="offer-card">
                <div class="offer-image" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80')"></div>
                <div class="offer-content">
                    <span class="offer-badge">Student Deal</span>
                    <h3 class="offer-title">Student Special</h3>
                    <p class="offer-description">
                        Flash your student ID and get 25% off on tickets and 15% off concessions. Available all week long!
                    </p>
                    <div class="offer-details">
                        <p><i class="fas fa-calendar-alt"></i> Valid any day</p>
                        <p><i class="fas fa-ticket-alt"></i> All movies included</p>
                        <p><i class="fas fa-id-card"></i> Student ID required</p>
                    </div>
                    <button class="claim-btn">Claim Offer <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            
            <h2 class="section-title">Combo Deals</h2>
            
            <div class="offer-card">
                <div class="offer-image" style="background-image: url('https://images.unsplash.com/photo-1566438480900-0609be27a4be?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1494&q=80')"></div>
                <div class="offer-content">
                    <span class="offer-badge">Popular</span>
                    <h3 class="offer-title">Family Package</h3>
                    <p class="offer-description">
                       Buy 4 movie tickets + 2 large popcorns + 4 fountain drinks and get 15% off
                    </p>
                    <div class="offer-details">
                        <p><i class="fas fa-calendar-alt"></i> Valid any day</p>
                        <p><i class="fas fa-film"></i> Standard screenings only</p>
                        <p><i class="fas fa-users"></i> Perfect for families</p>
                    </div>
                    <button class="claim-btn">Claim Offer <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            
            <div class="offer-card">
                <div class="offer-image" style="background-image: url('https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80')"></div>
                <div class="offer-content">
                    <span class="offer-badge">New</span>
                    <h3 class="offer-title">Date Night Special</h3>
                    <p class="offer-description">
                        Get 2 movie tickets + 1 large popcorn to share + 2 drinks + 1 candy box and get 5% off
                    </p>
                    <div class="offer-details">
                        <p><i class="fas fa-calendar-alt"></i> Friday-Sunday evenings</p>
                        <p><i class="fas fa-heart"></i> Perfect for couples</p>
                        <p><i class="fas fa-clock"></i> After 5pm only</p>
                    </div>
                    <button class="claim-btn">Claim Offer <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            
            <h2 class="section-title">Loyalty Rewards</h2>
            
            <div class="offer-card">
                <div class="offer-image" style="background-image: url('https://images.unsplash.com/photo-1556740738-b6a63e27c4df?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80')"></div>
                <div class="offer-content">
                    <span class="offer-badge">Members Only</span>
                    <h3 class="offer-title">5th Visit Free</h3>
                    <p class="offer-description">
                        Join our loyalty program and get your 5th movie ticket free! Earn points with every purchase.
                    </p>
                    <div class="offer-details">
                        <p><i class="fas fa-star"></i> Earn 1 point per ₦1,000 spent</p>
                        <p><i class="fas fa-ticket-alt"></i> 50 points = free ticket</p>
                        <p><i class="fas fa-gift"></i> Birthday freebie</p>
                    </div>
                    <button class="claim-btn">Join Now <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </main>

    <!--Footer-->
    <?php require_once ('footer.php')?>
</body>
</html>