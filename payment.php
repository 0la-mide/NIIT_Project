
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. Validate all required data
$required_fields = ['showtime_id', 'selected_seats', 'total_price', 'fullname', 'email', 'phone', 'cinema_id', 'auditorium_id'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Missing required field: $field");
    }
}

// 2. Database connection
require_once __DIR__ . '/config.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 3. Verify showtime still exists and hasn't started
$showtime_id = (int)$_POST['showtime_id'];
$sql = "SELECT s.*, m.title, a.name AS auditorium 
        FROM showtimes s
        JOIN movies m ON s.movie_id = m.movie_id
        JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
        WHERE s.showtime_id = ? AND s.start_time > NOW()";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) die("Showtime not found or already started.");
$showtime = $result->fetch_assoc();
$stmt->close();

// 4. Process selected seats
$selected_seats = json_decode($_POST['selected_seats'], true);
if (!is_array($selected_seats) || empty($selected_seats)) {
    die("No seats selected");
}

// 5. Check if seats are still available
$booked_seats = [];
$sql = "SELECT seat_number FROM bookings WHERE showtime_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $seats = json_decode($row['seat_number'], true);
    if (is_array($seats)) $booked_seats = array_merge($booked_seats, $seats);
}
$stmt->close();

foreach ($selected_seats as $seat) {
    if (in_array($seat, $booked_seats)) {
        die("Seat $seat is no longer available. Please go back and select different seats.");
    }
}

// 6. Process food items if provided
$food_subtotal = 0;
$food_items_data = [];
if (!empty($_POST['food_items'])) {
    $food_items_data = json_decode($_POST['food_items'], true);
    if (is_array($food_items_data)) {
        foreach ($food_items_data as $food_id => $quantity) {
            if ($quantity > 0) {
                $food_stmt = $conn->prepare("SELECT price FROM food_items WHERE item_id = ?");
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
}

// 7. Process payment if form submitted
$payment_processed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_number'])) {
    // Simulate payment processing
    $card_number = str_replace(' ', '', $_POST['card_number']);
    $card_expiry = $_POST['card_expiry'];
    $card_cvv = $_POST['card_cvv'];
    
    // Simple validation
    if (strlen($card_number) < 16 || !is_numeric($card_number)) {
        $payment_error = "Invalid card number";
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $card_expiry)) {
        $payment_error = "Invalid expiry date (MM/YY)";
    } elseif (strlen($card_cvv) < 3 || !is_numeric($card_cvv)) {
        $payment_error = "Invalid CVV";
    } else {
        // Payment "successful"
        $payment_processed = true;
        
        // Calculate subtotal and service fee
        $tickets_subtotal = $_POST['tickets_subtotal'] ?? ($_POST['total_price'] - 200);
        $service_fee = $_POST['service_fee'] ?? 200;
        $booking_reference = 'BK' . strtoupper(uniqid());
        
        // Create booking record
        $sql = "INSERT INTO bookings (
            user_id, guest_email, guest_name, showtime_id, booking_date, 
            total_amount, booking_reference, status, guest_phone, seat_number, 
            tickets_subtotal, service_fee, food_subtotal, cinema_id, auditorium_id, movie_title, 
            showtime_datetime, food_items
        ) VALUES (
            NULL, ?, ?, ?, NOW(), 
            ?, ?, 'confirmed', ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?
        )";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $seats_json = json_encode($selected_seats);
        $showtime_datetime = $showtime['start_time'];
        $food_items_json = !empty($food_items_data) ? json_encode($food_items_data) : null;

        // FIXED bind_param: 15 placeholders = 15 variables
        $stmt->bind_param(
            "sssdsssddisssss",
            $_POST['email'],        // s
            $_POST['fullname'],     // s
            $showtime_id,           // s (int, can still go as s or i, but using d/i is better)
            $_POST['total_price'],  // d
            $booking_reference,     // s
            $_POST['phone'],        // s
            $seats_json,            // s
            $tickets_subtotal,      // d
            $service_fee,           // d
            $food_subtotal,         // i (if always integer) or d (if decimal)
            $_POST['cinema_id'],    // s (or i)
            $_POST['auditorium_id'],// s (or i)
            $showtime['title'],     // s
            $showtime_datetime,     // s
            $food_items_json        // s (nullable JSON string)
        );

        if (!$stmt->execute()) {
            die("Failed to create booking: " . $stmt->error);
        }
        
        $booking_id = $stmt->insert_id;
        $stmt->close();
        
        // Send confirmation email (simulated)
        $to = $_POST['email'];
        $subject = "Your Movie Tickets for " . $showtime['title'];
        $message = "Dear " . $_POST['fullname'] . ",\n\n";
        $message .= "Thank you for your booking!\n\n";
        $message .= "Movie: " . $showtime['title'] . "\n";
        $message .= "Showtime: " . date('l, F j, Y \a\t g:i A', strtotime($showtime['start_time'])) . "\n";
        $message .= "Auditorium: " . $_POST['auditorium_name'] . "\n";
        $message .= "Seats: " . implode(', ', $selected_seats) . "\n";
        
        // Add food items if any
        if ($food_subtotal > 0) {
            $message .= "\nFood & Drinks:\n";
            foreach ($food_items_data as $food_id => $quantity) {
                if ($quantity > 0) {
                    $food_stmt = $conn->prepare("SELECT name, price FROM food_items WHERE item_id = ?");
                    $food_stmt->bind_param("i", $food_id);
                    $food_stmt->execute();
                    $food_result = $food_stmt->get_result();
                    
                    if ($food_result->num_rows > 0) {
                        $food_item = $food_result->fetch_assoc();
                        $message .= "  - " . $food_item['name'] . " x" . $quantity . " (₦" . number_format($food_item['price'] * $quantity, 2) . ")\n";
                    }
                    $food_stmt->close();
                }
            }
        }
        
        $message .= "\nTickets Subtotal: ₦" . number_format($tickets_subtotal, 2) . "\n";
        if ($food_subtotal > 0) {
            $message .= "Food & Drinks: ₦" . number_format($food_subtotal, 2) . "\n";
        }
        $message .= "Service Fee: ₦" . number_format($service_fee, 2) . "\n";
        $message .= "Total Paid: ₦" . number_format($_POST['total_price'], 2) . "\n";
        $message .= "Booking Reference: " . $booking_reference . "\n\n";
        $message .= "Enjoy your movie!\n";
        
        // In a real system, we would use PHPMailer here
        // For simulation, we'll store it in session
        $_SESSION['last_booking_email'] = $message;
        $_SESSION['last_booking_id'] = $booking_id;
        
        // Redirect to confirmation page
        header("Location: booking_confirmation.php?booking_id=$booking_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?= htmlspecialchars($showtime['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b2070f;
            --secondary: #221f1f;
            --light: #f5f5f5;
            --dark: #000000;
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
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background: rgba(229, 9, 20, 0.1);
        }
        
        .flow-steps {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
            counter-reset: step;
        }
        
        .flow-steps:before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #ddd;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .step:before {
            counter-increment: step;
            content: counter(step);
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: #ddd;
            border-radius: 50%;
            color: #666;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .step.active:before {
            background: var(--primary);
            color: white;
        }
        
        .step.completed:before {
            content: "✓";
            background: var(--primary);
            color: white;
        }
        
        .step i {
            font-size: 18px;
            margin-bottom: 5px;
            display: none;
        }
        
        .step span {
            font-size: 14px;
            color: #666;
        }
        
        .step.active span, .step.completed span {
            color: var(--secondary);
            font-weight: 500;
        }
        
        .booking-summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .selected-seats {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .price-summary {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-price {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .payment-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .payment-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .payment-header i {
            font-size: 28px;
            color: var(--primary);
        }
        
        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: rgba(229, 9, 20, 0.05);
        }
        
        .payment-method i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #666;
        }
        
        .payment-method.selected i {
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .card-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
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
            width: 100%;
            font-size: 16px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
        }
        
        .error-message {
            color: var(--primary);
            margin-top: 5px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .payment-methods {
                flex-direction: column;
            }
            
            .step:before {
                display: none;
            }
            
            .step i {
                display: block;
            }
            
            .step span {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?><br><br><br><br><br><br>
    <div class="container">
        <a href="booking.php?showtime_id=<?= $showtime_id ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <!-- Progress Steps -->
        <div class="flow-steps">
            <div class="step completed">
                <i class="fas fa-chair"></i> 
                <span>Select Seats</span>
            </div>
            <div class="step completed">
                <i class="fas fa-utensils"></i> 
                <span>Food & Drinks</span>
            </div>
            <div class="step completed">
                <i class="fas fa-user"></i> 
                <span>Guest Info</span>
            </div>
            <div class="step active">
                <i class="fas fa-credit-card"></i> 
                <span>Payment</span>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
            <h3><i class="fas fa-ticket-alt"></i> Booking Summary</h3>
            <div class="selected-seats">
                <strong>Movie:</strong> <?= htmlspecialchars($showtime['title']) ?><br>
                <strong>Showtime:</strong> <?= date('l, F j, Y \a\t g:i A', strtotime($showtime['start_time'])) ?><br>
                <strong>Auditorium:</strong> <?= htmlspecialchars($_POST['auditorium_name']) ?><br>
                <strong>Seats:</strong> <?= implode(', ', $selected_seats) ?>
            </div>
            <?php if ($food_subtotal > 0): ?>
            <div class="selected-seats" style="margin-top: 10px;">
                <strong>Food & Drinks:</strong>
                <?php 
                $food_details = [];
                foreach ($food_items_data as $food_id => $quantity):
                    if ($quantity > 0):
                        $food_stmt = $conn->prepare("SELECT name, price FROM food_items WHERE item_id = ?");
                        $food_stmt->bind_param("i", $food_id);
                        $food_stmt->execute();
                        $food_result = $food_stmt->get_result();
                        
                        if ($food_result->num_rows > 0):
                            $food_item = $food_result->fetch_assoc();
                            $food_details[] = $food_item['name'] . " x" . $quantity . " (₦" . number_format($food_item['price'] * $quantity, 2) . ")";
                        endif;
                        $food_stmt->close();
                    endif;
                endforeach;
                echo implode(", ", $food_details);
                ?>
            </div>
            <?php endif; ?>
            <div class="price-summary">
                <div class="summary-item">
                    <span>Tickets (<?= count($selected_seats) ?>):</span>
                    <span>₦<?= number_format($_POST['tickets_subtotal'] ?? ($_POST['total_price'] - 200), 2) ?></span>
                </div>
                <?php if ($food_subtotal > 0): ?>
                <div class="summary-item">
                    <span>Food & Drinks:</span>
                    <span>₦<?= number_format($food_subtotal, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-item">
                    <span>Service Fee:</span>
                    <span>₦<?= number_format($_POST['service_fee'] ?? 200, 2) ?></span>
                </div>
                <div class="total-price">
                    <span>Total:</span>
                    <span>₦<?= number_format($_POST['total_price'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-form">
            <div class="payment-header">
                <i class="fas fa-credit-card"></i>
                <h2>Payment Details</h2>
            </div>
            
            <?php if (isset($payment_error)): ?>
                <div class="error-message" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($payment_error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="showtime_id" value="<?= htmlspecialchars($_POST['showtime_id']) ?>">
                <input type="hidden" name="selected_seats" value="<?= htmlspecialchars($_POST['selected_seats']) ?>">
                <input type="hidden" name="total_price" value="<?= htmlspecialchars($_POST['total_price']) ?>">
                <input type="hidden" name="fullname" value="<?= htmlspecialchars($_POST['fullname']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
                <input type="hidden" name="phone" value="<?= htmlspecialchars($_POST['phone']) ?>">
                <input type="hidden" name="cinema_id" value="<?= htmlspecialchars($_POST['cinema_id']) ?>">
                <input type="hidden" name="auditorium_id" value="<?= htmlspecialchars($_POST['auditorium_id']) ?>">
                <input type="hidden" name="auditorium_name" value="<?= htmlspecialchars($_POST['auditorium_name']) ?>">
                <input type="hidden" name="movie_title" value="<?= htmlspecialchars($_POST['movie_title']) ?>">
                <input type="hidden" name="showtime_datetime" value="<?= htmlspecialchars($_POST['showtime_datetime']) ?>">
                <input type="hidden" name="tickets_subtotal" value="<?= htmlspecialchars($_POST['tickets_subtotal'] ?? ($_POST['total_price'] - 200)) ?>">
                <input type="hidden" name="service_fee" value="<?= htmlspecialchars($_POST['service_fee'] ?? 200) ?>">
                <input type="hidden" name="food_items" value="<?= htmlspecialchars($_POST['food_items'] ?? '') ?>">
                
                <div class="payment-methods">
                    <div class="payment-method selected">
                        <i class="fab fa-cc-visa"></i>
                        <div>Credit/Debit Card</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <div style="position: relative;">
                        <input type="text" id="card_number" name="card_number" class="form-control" 
                               placeholder="1234 5678 9012 3456" maxlength="19" required>
                        <i class="fas fa-credit-card card-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" name="card_name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['fullname']) ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="card_expiry">Expiry Date</label>
                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" 
                               placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label for="card_cvv">CVV</label>
                        <input type="text" id="card_cvv" name="card_cvv" class="form-control" 
                               placeholder="123" maxlength="4" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    Pay ₦<?= number_format($_POST['total_price'], 2) ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });
        
        // Format expiry date
        document.getEleNmentById('card_expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>