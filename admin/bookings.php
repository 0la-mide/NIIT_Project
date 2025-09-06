<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Initialize search variables
$search_term = '';
$where_clause = '';

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $safe_search_term = $conn->real_escape_string($search_term);
    $where_clause = "WHERE b.booking_reference LIKE '%$safe_search_term%' 
                     OR b.guest_name LIKE '%$safe_search_term%' 
                     OR b.guest_email LIKE '%$safe_search_term%' 
                     OR m.title LIKE '%$safe_search_term%'";
}

// Get all bookings with related data
$query = "
    SELECT b.*, 
           m.title as movie_title,
           a.name as auditorium_name,
           c.name as cinema_name,
           s.start_time as showtime_datetime
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    $where_clause
    ORDER BY b.booking_date DESC, b.booking_id DESC
";

$bookings = $conn->query($query);

// Include header
include __DIR__ . '/header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Bookings</h1>
            <div class="admin-actions">
                <!-- Search Form -->
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search bookings..." value="<?= htmlspecialchars($search_term) ?>" 
                               class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="bookings.php" class="clear-search" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Search Results Info -->
        <?php if (!empty($search_term)): ?>
            <div class="search-results-info">
                <p>Showing results for: <strong>"<?= htmlspecialchars($search_term) ?>"</strong></p>
                <a href="bookings.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Clear search
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Bookings List -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Movie</th>
                        <th>Cinema</th>
                        <th>Auditorium</th>
                        <th>Showtime</th>
                        <th>Customer</th>
                        <th>Seats</th>
                        <th>Total (₦)</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings->num_rows > 0): ?>
                        <?php while ($booking = $bookings->fetch_assoc()): 
                            $seats = json_decode($booking['seat_number'], true);
                        ?>
                        <tr>
                            <td>
                                <span class="booking-ref"><?= htmlspecialchars($booking['booking_reference']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($booking['movie_title']) ?></td>
                            <td><?= htmlspecialchars($booking['cinema_name']) ?></td>
                            <td><?= htmlspecialchars($booking['auditorium_name']) ?></td>
                            <td><?= date('M j, Y g:i a', strtotime($booking['showtime_datetime'])) ?></td>
                            <td>
                                <div class="customer-info">
                                    <strong><?= htmlspecialchars($booking['guest_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($booking['guest_email']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="seats-badge">
                                    <?= is_array($seats) ? implode(', ', $seats) : htmlspecialchars($booking['seat_number']) ?>
                                </span>
                            </td>
                            <td class="text-nowrap">₦<?= number_format($booking['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-badge status-confirmed">Confirmed</span>
                            </td>
                            <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                            <td class="actions">
                                <a href="view_booking.php?id=<?= $booking['booking_id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">
                                <?php if (!empty($search_term)): ?>
                                    <div class="no-results">
                                        <i class="fas fa-search fa-2x"></i>
                                        <h3>No bookings found</h3>
                                        <p>No bookings match your search for "<?= htmlspecialchars($search_term) ?>"</p>
                                        <a href="bookings.php" class="btn btn-primary">
                                            View all bookings
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-results">
                                        <i class="fas fa-ticket-alt fa-2x"></i>
                                        <h3>No bookings yet</h3>
                                        <p>There are no bookings in the system.</p>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Search Styles */
    .search-form {
        margin-bottom: 0;
    }
    
    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .search-input {
        padding: 10px 40px 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 25px;
        width: 300px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }
    
    .search-btn {
        position: absolute;
        right: 35px;
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
    }
    
    .clear-search {
        position: absolute;
        right: 10px;
        color: #6c757d;
        text-decoration: none;
        padding: 5px;
    }
    
    .search-btn:hover,
    .clear-search:hover {
        color: #007bff;
    }
    
    /* Search Results Info */
    .search-results-info {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Table Improvements */
    .table-responsive {
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
    }
    
    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
    }
    
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    /* Badges */
    .booking-ref {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #007bff;
        background: #e3f2fd;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .seats-badge {
        background: #e8f5e8;
        color: #2e7d32;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-confirmed {
        background: #e8f5e8;
        color: #2e7d32;
    }
    
    /* Customer Info */
    .customer-info {
        line-height: 1.4;
    }
    
    .customer-info small {
        color: #6c757d;
        font-size: 12px;
    }
    
    /* No Results */
    .no-results {
        padding: 40px;
        text-align: center;
        color: #6c757d;
    }
    
    .no-results i {
        margin-bottom: 15px;
        color: #dee2e6;
    }
    
    .no-results h3 {
        margin-bottom: 10px;
        color: #495057;
    }
    
    /* Admin Header Improvements */
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .admin-title {
        margin: 0;
        color: #2c3e50;
    }
    
    .admin-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .search-input {
            width: 200px;
        }
        
        .admin-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .admin-actions {
            justify-content: center;
        }
        
        .data-table th,
        .data-table td {
            padding: 10px;
            font-size: 14px;
        }
        
        .search-results-info {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
    }
    
    @media (max-width: 576px) {
        .search-input {
            width: 100%;
        }
        
        .search-input-group {
            width: 100%;
        }
    }
</style>

</body>
</html>