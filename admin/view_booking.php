<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];

// Fetch booking details with related data
$stmt = $conn->prepare("
    SELECT b.*, 
           m.title as movie_title,
           m.poster_img as poster_url,
           a.name as auditorium_name,
           c.name as cinema_name,
           c.location as cinema_location,
           s.start_time as showtime_datetime,
           s.format
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.showtime_id
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    WHERE b.booking_id = ?
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: bookings.php');
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Decode seat numbers and food items
$selected_seats = json_decode($booking['seat_number'], true);
$food_items = !empty($booking['food_items']) ? json_decode($booking['food_items'], true) : [];

// Calculate food subtotal if needed
$food_subtotal = 0;
if (!empty($food_items)) {
    foreach ($food_items as $food_id => $quantity) {
        if ($quantity > 0) {
            $food_stmt = $conn->prepare("SELECT name, price FROM food_items WHERE item_id = ?");
            $food_stmt->bind_param("i", $food_id);
            $food_stmt->execute();
            $food_result = $food_stmt->get_result();
            
            if ($food_result->num_rows > 0) {
                $food_item = $food_result->fetch_assoc();
                $food_subtotal += $food_item['price'] * $quantity;
            }
            $food_stmt->close();
        }
    }
}

// Include header
include __DIR__ . '/header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Booking Details</h1>
                <p class="admin-subtitle">Booking Reference: <?= htmlspecialchars($booking['booking_reference']) ?></p>
            </div>
            <div class="admin-actions">
                <a href="bookings.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </div>

        <div class="booking-details-container">
            <!-- Booking Summary Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-ticket-alt"></i> Booking Summary</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Booking Reference</span>
                            <span class="detail-value booking-ref"><?= htmlspecialchars($booking['booking_reference']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Booking Date</span>
                            <span class="detail-value"><?= date('F j, Y g:i a', strtotime($booking['booking_date'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="status-badge status-confirmed">Confirmed</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Amount</span>
                            <span class="detail-value price">₦<?= number_format($booking['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Full Name</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['guest_name']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email Address</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['guest_email']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone Number</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['guest_phone'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movie & Showtime Information -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-film"></i> Movie & Showtime</h3>
                </div>
                <div class="detail-card-body">
                    <div class="movie-info">
                        <?php if (!empty($booking['poster_url'])): ?>
                        <img src="../<?= htmlspecialchars($booking['poster_url']) ?>" alt="<?= htmlspecialchars($booking['movie_title']) ?>" class="movie-poster" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div class="movie-details">
                            <h4><?= htmlspecialchars($booking['movie_title']) ?></h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Showtime</span>
                                    <span class="detail-value"><?= date('l, F j, Y \a\t g:i A', strtotime($booking['showtime_datetime'])) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Format</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['format'] ?? 'Standard') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Cinema</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['cinema_name']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Location</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['cinema_location']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Auditorium</span>
                                    <span class="detail-value"><?= htmlspecialchars($booking['auditorium_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seating Information -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-chair"></i> Seating Information</h3>
                </div>
                <div class="detail-card-body">
                    <div class="seats-container">
                        <?php foreach ($selected_seats as $seat): ?>
                            <span class="seat-badge"><?= $seat ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Seats</span>
                        <span class="detail-value"><?= count($selected_seats) ?> seat(s)</span>
                    </div>
                </div>
            </div>

            <!-- Food & Beverages -->
            <?php if (!empty($food_items)): ?>
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-utensils"></i> Food & Beverages</h3>
                </div>
                <div class="detail-card-body">
                    <div class="food-items">
                        <?php foreach ($food_items as $food_id => $quantity): ?>
                            <?php if ($quantity > 0): ?>
                                <?php
                                $food_stmt = $conn->prepare("SELECT name, price FROM food_items WHERE item_id = ?");
                                $food_stmt->bind_param("i", $food_id);
                                $food_stmt->execute();
                                $food_result = $food_stmt->get_result();
                                
                                if ($food_result->num_rows > 0):
                                    $food_item = $food_result->fetch_assoc();
                                    $item_total = $food_item['price'] * $quantity;
                                ?>
                                <div class="food-item">
                                    <span class="food-name"><?= htmlspecialchars($food_item['name']) ?> ×<?= $quantity ?></span>
                                    <span class="food-price">₦<?= number_format($item_total, 2) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php $food_stmt->close(); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment Summary -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-receipt"></i> Payment Summary</h3>
                </div>
                <div class="detail-card-body">
                    <div class="payment-summary">
                        <div class="payment-item">
                            <span class="payment-label">Tickets Subtotal</span>
                            <span class="payment-value">₦<?= number_format($booking['tickets_subtotal'], 2) ?></span>
                        </div>
                        <?php if ($food_subtotal > 0): ?>
                        <div class="payment-item">
                            <span class="payment-label">Food & Beverages</span>
                            <span class="payment-value">₦<?= number_format($food_subtotal, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="payment-item">
                            <span class="payment-label">Service Fee</span>
                            <span class="payment-value">₦<?= number_format($booking['service_fee'], 2) ?></span>
                        </div>
                        <div class="payment-item total">
                            <span class="payment-label">Total Paid</span>
                            <span class="payment-value">₦<?= number_format($booking['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .booking-details-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .detail-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .detail-card-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .detail-card-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 18px;
    }
    
    .detail-card-header i {
        color: #007bff;
        margin-right: 10px;
    }
    
    .detail-card-body {
        padding: 20px;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .detail-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .detail-value {
        font-size: 16px;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .booking-ref {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #007bff;
        background: #e3f2fd;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    
    .price {
        color: #28a745;
        font-weight: bold;
        font-size: 18px;
    }
    
    .movie-info {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    
    .movie-poster {
        width: 100px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .movie-details {
        flex: 1;
    }
    
    .movie-details h4 {
        margin: 0 0 15px 0;
        color: #2c3e50;
        font-size: 20px;
    }
    
    .seats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .seat-badge {
        background: #28a745;
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .food-items {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .food-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .food-name {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .food-price {
        color: #28a745;
        font-weight: 500;
    }
    
    .payment-summary {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
    }
    
    .payment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .payment-item.total {
        border-top: 2px solid #007bff;
        border-bottom: none;
        padding-top: 15px;
        margin-top: 5px;
    }
    
    .payment-label {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .payment-value {
        font-weight: 500;
        color: #28a745;
    }
    
    .payment-item.total .payment-value {
        font-size: 18px;
        font-weight: bold;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-confirmed {
        background: #e8f5e8;
        color: #2e7d32;
    }
    
    .admin-subtitle {
        color: #6c757d;
        margin: 5px 0 0 0;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .movie-info {
            flex-direction: column;
            text-align: center;
        }
        
        .movie-poster {
            align-self: center;
        }
        
        .detail-grid {
            grid-template-columns: 1fr;
        }
        
        .admin-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .admin-actions {
            justify-content: flex-start;
        }
    }
    
    @media print {
        .admin-container {
            margin: 0;
            padding: 0;
        }
        
        .sidebar {
            display: none;
        }
        
        .admin-content {
            margin-left: 0;
            width: 100%;
        }
        
        .admin-actions {
            display: none;
        }
        
        .detail-card {
            box-shadow: none;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
    }
</style>

</body>
</html>