<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
require_once __DIR__ . '/config.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_GET['booking_id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = (int)$_GET['booking_id'];

// Fetch booking details from database
$sql = "SELECT b.*, a.name as auditorium_name, c.name as cinema_name, c.location as cinema_location
        FROM bookings b 
        LEFT JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        LEFT JOIN cinemas c ON a.cinema_id = c.cinema_id
        WHERE b.booking_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found.");
}

$booking = $result->fetch_assoc();
$stmt->close();

// Decode seat numbers and food items
$selected_seats = json_decode($booking['seat_number'], true);
$food_items = !empty($booking['food_items']) ? json_decode($booking['food_items'], true) : [];

// Generate QR code URL using a CDN service
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($booking['booking_reference']);

// Build confirmation message
$confirmation_message = "Dear " . htmlspecialchars($booking['guest_name']) . ",\n\n";
$confirmation_message .= "Thank you for your booking!\n\n";
$confirmation_message .= "Booking Reference: " . htmlspecialchars($booking['booking_reference']) . "\n";
$confirmation_message .= "Movie: " . htmlspecialchars($booking['movie_title']) . "\n";
$confirmation_message .= "Showtime: " . date('l, F j, Y \a\t g:i A', strtotime($booking['showtime_datetime'])) . "\n";
$confirmation_message .= "Auditorium: " . htmlspecialchars($booking['auditorium_name']) . " at " . 
                         htmlspecialchars($booking['cinema_name']) . ", " . 
                         htmlspecialchars($booking['cinema_location']) . "\n";
$confirmation_message .= "Seats: " . implode(', ', $selected_seats) . "\n";

// Add food items if any
if (!empty($food_items)) {
    $confirmation_message .= "\nFood & Drinks:\n";
    $food_subtotal = 0;
    
    foreach ($food_items as $food_id => $quantity) {
        if ($quantity > 0) {
            $food_stmt = $conn->prepare("SELECT name, price FROM food_items WHERE item_id = ?");
            $food_stmt->bind_param("i", $food_id);
            $food_stmt->execute();
            $food_result = $food_stmt->get_result();
            
            if ($food_result->num_rows > 0) {
                $food_item = $food_result->fetch_assoc();
                $item_total = $food_item['price'] * $quantity;
                $food_subtotal += $item_total;
                $confirmation_message .= "  - " . $food_item['name'] . " x" . $quantity . " (₦" . number_format($item_total, 2) . ")\n";
            }
            $food_stmt->close();
        }
    }
}

$confirmation_message .= "\nTickets Subtotal: ₦" . number_format($booking['tickets_subtotal'], 2) . "\n";
if ($booking['food_subtotal'] > 0) {
    $confirmation_message .= "Food & Drinks: ₦" . number_format($booking['food_subtotal'], 2) . "\n";
}
$confirmation_message .= "Service Fee: ₦" . number_format($booking['service_fee'], 2) . "\n";
$confirmation_message .= "Total Paid: ₦" . number_format($booking['total_amount'], 2) . "\n\n";
$confirmation_message .= "Enjoy your movie!\n";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add HTML2Canvas library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b2070f;
            --secondary: #221f1f;
            --light: #f5f5f5;
            --dark: #000000;
            --gold: #ffd700;
        }
        
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        
        .confirmation-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 40px 0;
        }
        
        .confirmation-icon {
            font-size: 60px;
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
        }
        
        .confirmation-details {
            text-align: left;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            white-space: pre-line;
        }
        
        .booking-reference {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--primary);
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            display: inline-block;
        }
        
        .ticket-download {
            background: #4CAF50;
            margin-left: 10px;
        }
        
        .ticket-download:hover {
            background: #3e8e41;
        }
        
        .download-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        
        .download-notice h3 {
            color: #856404;
            margin-top: 0;
        }
        
        /* Ticket Styling */
        .ticket {
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-left: 5px solid var(--gold);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px dashed rgba(255,255,255,0.2);
            padding-bottom: 15px;
        }
        
        .ticket-logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--gold);
        }
        
        .ticket-logo img {
            max-width: 150px;
            height: auto;
        }
        
        .ticket-qr {
            background: white;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .ticket-qr img {
            width: 120px;
            height: 120px;
        }
        
        .ticket-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: left;
        }
        
        .ticket-info div {
            margin-bottom: 10px;
        }
        
        .ticket-label {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 5px;
        }
        
        .ticket-value {
            font-weight: bold;
            font-size: 16px;
        }
        
        .ticket-movie {
            grid-column: 1 / -1;
            text-align: center;
            margin: 15px 0;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        
        .ticket-movie-title {
            font-size: 22px;
            font-weight: bold;
            color: var(--gold);
            margin-bottom: 5px;
        }
        
        .ticket-seats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .seat-badge {
            background: var(--gold);
            color: #333;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .ticket-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed rgba(255,255,255,0.2);
            font-size: 12px;
            color: #aaa;
        }
        
        /* Loading overlay for download */
        .download-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        
        .download-spinner {
            color: white;
            font-size: 24px;
            text-align: center;
        }
        
        .download-spinner i {
            display: block;
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .confirmation-box {
                padding: 20px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }
            
            .ticket-info {
                grid-template-columns: 1fr;
            }
            
            .ticket-header {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        @media print {
            .btn {
                display: none;
            }
            
            .confirmation-box {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            
            body {
                background: white;
            }
            
            .download-notice {
                display: none;
            }
            
            .ticket {
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?><br><br><br><br><br><br>
    <div class="container">
        <div class="confirmation-box">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Booking Confirmed!</h1>
            <p>Your payment was successful and your tickets have been booked.</p>
            
            <!-- Download instructions -->
            <div class="download-notice">
                <h3><i class="fas fa-exclamation-circle"></i> Important Notice</h3>
                <p><strong>Please download your ticket for your records.</strong> If there are any errors in your email address 
                or other contact details, having your ticket downloaded ensures you won't lose access to your booking.</p>
                <p>Your ticket contains a QR code that will be scanned at the theater for entry.</p>
            </div>
            
            <div class="booking-reference">
                <i class="fas fa-ticket-alt"></i> Booking Reference: <?= htmlspecialchars($booking['booking_reference']) ?>
            </div>
            
            <!-- Ticket Design -->
            <div class="ticket" id="ticket-to-download">
                <div class="ticket-header">
                    <div class="ticket-logo"><img src="assets/logo.png" width="100%"></div>
                    <div class="ticket-qr">
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code">
                    </div>
                </div>
                
                <div class="ticket-movie">
                    <div class="ticket-movie-title"><?= htmlspecialchars($booking['movie_title']) ?></div>
                    <div class="ticket-showtime">
                        <?= date('l, F j, Y \a\t g:i A', strtotime($booking['showtime_datetime'])) ?>
                    </div>
                </div>
                
                <div class="ticket-info">
                    <div>
                        <div class="ticket-label">AUDITORIUM</div>
                        <div class="ticket-value"><?= htmlspecialchars($booking['auditorium_name']) ?></div>
                    </div>
                    
                    <div>
                        <div class="ticket-label">CINEMA</div>
                        <div class="ticket-value"><?= htmlspecialchars($booking['cinema_name']) ?></div>
                    </div>
                    
                    <div>
                        <div class="ticket-label">LOCATION</div>
                        <div class="ticket-value"><?= htmlspecialchars($booking['cinema_location']) ?></div>
                    </div>
                    
                    <div>
                        <div class="ticket-label">BOOKING REF</div>
                        <div class="ticket-value"><?= htmlspecialchars($booking['booking_reference']) ?></div>
                    </div>
                    
                    <div>
                        <div class="ticket-label">GUEST NAME</div>
                        <div class="ticket-value"><?= htmlspecialchars($booking['guest_name']) ?></div>
                    </div>
                    
                    <div>
                        <div class="ticket-label">TICKETS</div>
                        <div class="ticket-value"><?= count($selected_seats) ?> seat(s)</div>
                    </div>
                </div>
                
                <div class="ticket-seats">
                    <?php foreach ($selected_seats as $seat): ?>
                        <div class="seat-badge"><?= $seat ?></div>
                    <?php endforeach; ?>
                </div>
                
                <div class="ticket-footer">
                    <p>Please present this ticket at the theater. Valid ID may be required.</p>
                    <p>Scan QR code for verification.</p>
                </div>
            </div>
            
            <!-- Download overlay -->
            <div class="download-overlay" id="download-overlay">
                <div class="download-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Generating your ticket...</p>
                </div>
            </div>
            
            <div class="btn-group">
                <button onclick="downloadTicket()" class="btn ticket-download">
                    <i class="fas fa-download"></i> Download Ticket
                </button>
            </div><br>

            <p>A confirmation has been sent to your email address (<?= htmlspecialchars($booking['guest_email']) ?>).</p>
            
            <div class="confirmation-details">
                <?= nl2br(htmlspecialchars($confirmation_message)) ?>
            </div>
            
            <div class="btn-group">
                <a href="index.php" class="btn">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to confirmation after page load
        window.addEventListener('load', function() {
            document.querySelector('.confirmation-box').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        });
        
        // Download ticket function
        function downloadTicket() {
            // Show loading overlay
            document.getElementById('download-overlay').style.display = 'flex';
            
            // Use html2canvas to capture the ticket
            html2canvas(document.getElementById('ticket-to-download'), {
                scale: 2, // Higher quality
                useCORS: true, // Allow cross-origin images
                logging: false,
                backgroundColor: null // Transparent background
            }).then(function(canvas) {
                // Convert canvas to image data URL
                var imageData = canvas.toDataURL('image/jpeg', 0.9);
                
                // Create download link
                var link = document.createElement('a');
                link.href = imageData;
                link.download = 'ticket-<?= $booking['booking_reference'] ?>.jpg';
                
                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Hide loading overlay
                document.getElementById('download-overlay').style.display = 'none';
            }).catch(function(error) {
                console.error('Error generating ticket:', error);
                alert('Error generating ticket. Please try again or use the print function.');
                document.getElementById('download-overlay').style.display = 'none';
            });
        }
        
        // Alternative: Print function
        function printTicket() {
            var originalContents = document.body.innerHTML;
            var ticketContent = document.getElementById('ticket-to-download').innerHTML;
            
            document.body.innerHTML = '<div class="ticket-print">' + ticketContent + '</div>';
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload();
        }
    </script>
</body>
</html>