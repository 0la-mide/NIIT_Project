<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        .admin-sidebar {
            width: 250px;
            background-color: var(--secondary);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<nav class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Anvora Cinemas</h2>
        <small>Admin Panel</small>
    </div>
            
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="movies.php" class="menu-item">
            <i class="fas fa-film"></i> Movies
        </a>
        <a href="showtimes.php" class="menu-item">
            <i class="fas fa-calendar-alt"></i> Showtimes
        </a>
        <a href="cinemas.php" class="menu-item">
            <i class="fas fa-building"></i> Cinemas
        </a>
        <a href="auditoriums.php" class="menu-item">
            <i class="fas fa-chair"></i> Auditoriums
        </a>
        <a href="food-categories.php" class="menu-item">
            <i class="fas fa-hamburger"></i> Food Categories
        </a>
        <a href="food-items.php" class="menu-item">
            <i class="fas fa-hotdog"></i> Food Items
        </a>
        <a href="bookings.php" class="menu-item">
            <i class="fas fa-ticket-alt"></i> Bookings
        </a>
        <!--<a href="users.php" class="menu-item">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="reports.php" class="menu-item">
            <i class="fas fa-chart-bar"></i> Reports
        </a>-->
        <a href="../index.php" class="menu-item">
            <i class="fas fa-external-link-alt"></i> View Site
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>