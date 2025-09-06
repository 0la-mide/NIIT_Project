<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get dashboard statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(booking_date) = CURDATE()");
$stats['today_bookings'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE DATE(booking_date) = CURDATE()");
$stats['today_revenue'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as total FROM movies WHERE release_date <= CURDATE()");
$stats['current_movies'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM showtimes WHERE DATE(start_time) = CURDATE()");
$stats['today_showtimes'] = $result->fetch_assoc()['total'];

// Get recent bookings
$recentBookings = $conn->query("
    SELECT b.booking_reference, m.title, b.booking_date, b.total_amount, u.full_name 
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.user_id
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    JOIN movies m ON s.movie_id = m.movie_id
    ORDER BY b.booking_date DESC
    LIMIT 10
");

// Get upcoming showtimes
$upcomingShowtimes = $conn->query("
    SELECT s.start_time, m.title, a.name as auditorium, c.name as cinema
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    WHERE s.start_time >= NOW()
    ORDER BY s.start_time ASC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anvora Cinemas - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
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
        
        /* Main Content Styles */
        .admin-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-title {
            margin: 0;
            color: var(--secondary);
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: #666;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--secondary);
        }
        
        .stat-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        /* Dashboard Sections */
        .dashboard-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-title {
            margin: 0;
            font-size: 1.2rem;
            color: var(--secondary);
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background-color: #f9f9f9;
            font-weight: 500;
            color: var(--dark);
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        /* Chart Container */
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
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
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard</h1>
                <div class="admin-user">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_email']) ?>&background=random" alt="Admin">
                    <span><?= htmlspecialchars($_SESSION['admin_email']) ?></span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>Today's Bookings</h3>
                    <p><?= number_format($stats['today_bookings']) ?></p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-naira-sign"></i>
                    <h3>Today's Revenue</h3>
                    <p>₦<?= number_format($stats['today_revenue'], 2) ?></p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-film"></i>
                    <h3>Current Movies</h3>
                    <p><?= number_format($stats['current_movies']) ?></p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Today's Showtimes</h3>
                    <p><?= number_format($stats['today_showtimes']) ?></p>
                </div>
            </div>
            
            <!-- Revenue Chart -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Revenue Overview</h2>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Bookings</h2>
                    <a href="bookings.php" class="btn btn-outline">View All</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Movie</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $recentBookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_reference']) ?></td>
                            <td><?= htmlspecialchars($booking['title']) ?></td>
                            <td><?= $booking['full_name'] ? htmlspecialchars($booking['full_name']) : 'Guest' ?></td>
                            <td><?= date('M j, Y g:i a', strtotime($booking['booking_date'])) ?></td>
                            <td>₦<?= number_format($booking['total_amount'], 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Upcoming Showtimes -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Upcoming Showtimes</h2>
                    <a href="showtimes.php" class="btn btn-outline">View All</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Movie</th>
                            <th>Auditorium</th>
                            <th>Cinema</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($showtime = $upcomingShowtimes->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M j, Y g:i a', strtotime($showtime['start_time'])) ?></td>
                            <td><?= htmlspecialchars($showtime['title']) ?></td>
                            <td><?= htmlspecialchars($showtime['auditorium']) ?></td>
                            <td><?= htmlspecialchars($showtime['cinema']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Revenue (₦)',
                    data: [1250000, 1900000, 1530000, 1780000, 1920000, 2560000, 2340000, 1980000, 2230000, 2380000, 2890000, 3470000],
                    backgroundColor: 'rgba(229, 9, 20, 0.7)',
                    borderColor: 'rgba(229, 9, 20, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>