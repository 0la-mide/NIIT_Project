<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. Validate showtime_id
if (!isset($_GET['showtime_id']) || !ctype_digit($_GET['showtime_id'])) {
    die("Invalid showtime ID. Please go back and select a valid showtime.");
}
$showtime_id = (int)$_GET['showtime_id'];

// 2. Database connection
require_once __DIR__ . '/config.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 3. Fetch showtime details
$sql = "SELECT s.*, m.title, m.poster_img, m.duration, s.price, 
               a.name AS auditorium, a.seat_map, a.auditorium_id, a.cinema_id
        FROM showtimes s
        JOIN movies m ON s.movie_id = m.movie_id
        JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
        WHERE s.showtime_id = ? AND s.start_time > NOW()";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Database error: " . $conn->error);

$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) die("Showtime not found or already started.");
$showtime = $result->fetch_assoc();
$stmt->close();

// Calculate prices
$regular_price = $showtime['price'];
$vip_price = $showtime['price'] * 1.5;

// 4. Process seat map
$seat_map = json_decode($showtime['seat_map'], true) ?? [
    'rows' => 8,
    'cols' => 12,
    'map' => []
];

// 5. Fetch booked seats
$booked_seats = [];
$stmt = $conn->prepare("SELECT seat_number FROM bookings WHERE showtime_id = ?");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $seats = json_decode($row['seat_number'], true);
    if (is_array($seats)) $booked_seats = array_merge($booked_seats, $seats);
}
$stmt->close();

// 6. Fetch food items
$food_items = $conn->query("
    SELECT f.*, c.name as category_name, c.slug as category_slug 
    FROM food_items f
    JOIN food_categories c ON f.category_id = c.category_id
    WHERE f.is_active = 1
    ORDER BY c.name ASC, f.name ASC
");

// Group food items by category
$items_by_category = [];
while ($item = $food_items->fetch_assoc()) {
    $items_by_category[$item['category_name']][] = $item;
}

// Check current step
$current_step = 'seat_selection';
$seats_selected = false;
$food_selected = false;
$selected_seats = [];
$tickets_subtotal = 0;
$service_fee = 200; // Fixed service charge
$food_subtotal = 0;
$total_price = 0;
$food_items_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected_seats'])) {
        $selected_seats = json_decode($_POST['selected_seats'], true);
        if (!empty($selected_seats)) {
            $seats_selected = true;
            $tickets_subtotal = $showtime['price'] * count($selected_seats);
            $current_step = 'food_selection';
            
            // If user submitted food selection
            if (isset($_POST['food_items_json'])) {
                $food_items_data = json_decode($_POST['food_items_json'], true);
                if (!empty($food_items_data)) {
                    $food_selected = true;
                    $current_step = 'guest_info';
                    
                    // Calculate food items total
                    foreach ($food_items_data as $food_id => $quantity) {
                        if ($quantity > 0) {
                            // Get food item price from database
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
                } else {
                    $food_selected = false;
                    $current_step = 'guest_info';
                }
                
                $total_price = $tickets_subtotal + $service_fee + $food_subtotal;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if ($current_step === 'seat_selection') echo 'Select Seats';
        elseif ($current_step === 'food_selection') echo 'Food & Drinks Selection';
        else echo 'Guest Information';
        ?> - <?= htmlspecialchars($showtime['title']) ?>
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b2070f;
            --secondary: #221f1f;
            --light: #f5f5f5;
            --dark: #000000;
            --vip: #e67e22;
            --vip-dark: #d35400;
            --occupied: #95a5a6;
            --available: #2ecc71;
            --available-dark: #27ae60;
            --selected: var(--primary);
            --screen: #333;
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
            max-width: 1200px;
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

        /* Progress Steps */
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
            background: var(--primary);
            color: white;
            content: "✓";
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

        .step.active span,
        .step.completed span {
            color: var(--secondary);
            font-weight: 500;
        }

        .movie-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            align-items: center;
            flex-wrap: wrap;
        }

        .movie-poster {
            flex: 0 0 200px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .movie-poster img {
            width: 100%;
            height: auto;
            display: block;
        }

        .movie-info h1 {
            margin: 0 0 10px 0;
            color: var(--secondary);
        }

        .movie-meta {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            color: #666;
        }

        .screen-display {
            text-align: center;
            margin: 30px 0;
        }

        .screen-text {
            background: var(--screen);
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .screen-curve {
            height: 15px;
            width: 70%;
            margin: 0 auto;
            border-radius: 50%;
            box-shadow: 0 15px 10px -10px rgba(0,0,0,0.3);
        }

        .seat-legend {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 600px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            border-radius: 4px;
            background: white;
        }

        .seat {
            position: relative;
            width: 32px;
            height: 32px;
            background: #e8f5e9;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 11px;
            color: #333;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }
        .seat:before {
            content: "";
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 6px;
            background: #a5d6a7;
            border-radius: 0 0 3px 3px;
        }

        .seat.vip {
            background: #fff3e0;
        }
        .seat.vip:before {
            background: #ffcc80;
        }

        .seat.selected {
            background: #ffebee;
        }
        .seat.selected:before {
            background: #ef9a9a;
        }
        .seat.selected:after {
            content: "✓";
            position: absolute;
            color: var(--primary);
            font-weight: bold;
            font-size: 12px;
        }

        .seat.occupied {
            background: #efefef;
            cursor: not-allowed;
        }
        .seat.occupied:before {
            background: #bdbdbd;
        }
        .seat.occupied:after {
            content: "✕";
            position: absolute;
            color: #e53935;
            font-weight: bold;
            font-size: 12px;
        }

        .seat-map-container {
            overflow-x: auto;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .seat-map {
            display: inline-block;
            min-width: 100%;
        }

        .seat-row {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
            gap: 5px;
        }

        .row-label {
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
        }

        .booking-summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .selected-seats {
            background: white;
            padding: 15px;
            border-radius: 8px;
            min-height: 60px;
            margin: 15px 0;
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .movie-header {
                flex-direction: column;
                text-align: center;
            }
            
            .movie-poster {
                flex: 0 0 auto;
                width: 100%;
                max-width: 250px;
            }
            
            .movie-meta {
                justify-content: center;
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
            
            .seat {
                width: 28px;
                height: 28px;
                font-size: 10px;
            }
            
            .row-label {
                width: 25px;
                font-size: 12px;
            }
        }

        .section-container {
            display: none;
        }
        .section-container.active {
            display: block;
        }
        
        .guest-info-form {
            background: #f5f5f5;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
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
        
        .btn-secondary {
            background: #666;
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background: #555;
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

        /* Food & Drinks Styles */
        .food-section {
            margin: 30px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .food-section h3 {
            margin-bottom: 20px;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .food-categories {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .food-category-btn {
            background: #ddd;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .food-category-btn.active, .food-category-btn:hover {
            background: var(--primary);
            color: white;
        }

        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .food-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .food-item:hover {
            transform: translateY(-5px);
        }

        .food-item-img {
            height: 160px;
            width: 100%;
            object-fit: cover;
        }

        .food-item-details {
            padding: 15px;
        }

        .food-item-title {
            font-size: 1.1rem;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .food-item-price {
            color: var(--primary);
            font-weight: bold;
        }

        .food-item-desc {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
            height: 40px;
            overflow: hidden;
        }

        .food-quantity-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .quantity-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 40px;
            height: 30px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
        }

        .skip-food-btn {
            background: #666;
            margin-right: 10px;
        }

        .food-summary {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .food-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .food-summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .no-food-selected {
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
<?php require_once 'header.php';?><br><br><br><br><br><br>
    <div class="container">
        <a href="<?= $current_step === 'seat_selection' ? 'index.php' : 'booking.php?showtime_id='.$showtime_id ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <!-- Movie Header -->
        <div class="movie-header">
            <div class="movie-poster">
                <img src="<?= htmlspecialchars($showtime['poster_img']) ?>" alt="<?= htmlspecialchars($showtime['title']) ?>">
            </div>
            <div class="movie-info">
                <h1><?= htmlspecialchars($showtime['title']) ?></h1>
                <div class="movie-meta">
                    <span><i class="fas fa-clock"></i> <?= (int)$showtime['duration'] ?> mins</span>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($showtime['auditorium']) ?></span>
                </div>
                <p><?= date('l, F j, Y \a\t g:i A', strtotime($showtime['start_time'])) ?></p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="flow-steps">
            <div class="step <?= $current_step === 'seat_selection' ? 'active' : ($current_step !== 'seat_selection' ? 'completed' : '') ?>">
                <i class="fas fa-chair"></i> 
                <span>Select Seats</span>
            </div>
            <div class="step <?= $current_step === 'food_selection' ? 'active' : ($current_step === 'guest_info' ? 'completed' : '') ?>">
                <i class="fas fa-utensils"></i> 
                <span>Food & Drinks</span>
            </div>
            <div class="step <?= $current_step === 'guest_info' ? 'active' : '' ?>">
                <i class="fas fa-user"></i> 
                <span>Guest Info</span>
            </div>
            <div class="step">
                <i class="fas fa-credit-card"></i> 
                <span>Payment</span>
            </div>
        </div>

        <!-- Seat Selection Section -->
        <div id="seatSelectionSection" class="section-container <?= $current_step === 'seat_selection' ? 'active' : '' ?>">
            <div class="screen-display">
                <div class="screen-text">SCREEN</div>
                <div class="screen-curve"></div>
            </div>

            <div class="seat-map-container">
                <div class="seat-map">
                    <?php
                    $letters = range('A', 'Z');
                    for ($row = 0; $row < $seat_map['rows']; $row++): ?>
                        <div class="seat-row">
                            <div class="row-label"><?= $letters[$row] ?></div>
                            <?php for ($col = 1; $col <= $seat_map['cols']; $col++): 
                                $seat_id = $letters[$row] . $col;
                                $seat_type = $seat_map['map'][$seat_id]['type'] ?? (($row < 2) ? 'vip' : 'regular');
                                $is_booked = in_array($seat_id, $booked_seats);
                            ?>
                                <div class="seat <?= $seat_type === 'vip' ? 'vip' : '' ?> <?= $is_booked ? 'occupied' : 'available' ?>" 
                                     data-seat="<?= $seat_id ?>"
                                     data-type="<?= $seat_type ?>"
                                     onclick="selectSeat(this)">
                                    <?= $col ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="seat-legend">
                <div class="legend-item">
                    <div class="seat available"></div>
                    <span>Available (₦<?= number_format($regular_price, 2) ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="seat selected"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="seat occupied"></div>
                    <span>Occupied</span>
                </div>
            </div>

            <div class="booking-summary">
                <h3><i class="fas fa-ticket-alt"></i> Booking Summary</h3>
                <div class="selected-seats" id="selectedSeatsDisplay">
                    No seats selected yet
                </div>
                
                <form id="bookingForm" method="post">
                    <input type="hidden" name="selected_seats" id="selectedSeatsInput">
                    <button type="submit" class="btn" id="continueBtn" disabled>
                        Continue to Food & Drinks <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Food & Drinks Selection Section -->
        <div id="foodSelectionSection" class="section-container <?= $current_step === 'food_selection' ? 'active' : '' ?>">
            <div class="booking-summary">
                <h3><i class="fas fa-ticket-alt"></i> Booking Summary</h3>
                <div class="selected-seats">
                    <strong>Selected Seats:</strong> <?= implode(', ', $selected_seats) ?>
                </div>
                <div class="price-summary">
                    <div class="summary-item">
                        <span>Tickets (<?= count($selected_seats) ?>):</span>
                        <span>₦<?= number_format($tickets_subtotal, 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Service Fee:</span>
                        <span>₦<?= number_format($service_fee, 2) ?></span>
                    </div>
                    <div class="total-price">
                        <span>Subtotal:</span>
                        <span>₦<?= number_format($tickets_subtotal + $service_fee, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="food-section">
                <h3><i class="fas fa-utensils"></i> Enhance Your Experience</h3>
                <p>Select food and drinks to enjoy during your movie (optional)</p><br>
                
                <div class="food-categories">
                    <button class="food-category-btn active" data-category="all">All Items</button>
                    <?php 
                    $categories = array_keys($items_by_category);
                    foreach ($categories as $category): ?>
                        <button class="food-category-btn" data-category="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>">
                            <?= htmlspecialchars($category) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <div class="food-grid">
                    <?php foreach ($items_by_category as $category_name => $items): ?>
                        <?php foreach ($items as $item): ?>
                            <div class="food-item" data-category="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category_name))) ?>">
                                <img src="uploads/food/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="food-item-img">
                                <div class="food-item-details">
                                    <div class="food-item-title">
                                        <span><?= htmlspecialchars($item['name']) ?></span>
                                        <span class="food-item-price">₦<?= number_format($item['price'], 2) ?></span>
                                    </div>
                                    <p class="food-item-desc"><?= htmlspecialchars($item['description']) ?></p>
                                    <div class="food-quantity-controls">
                                        <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?= $item['item_id'] ?>)" disabled>-</button>
                                        <input type="number" id="food_quantity_<?= $item['item_id'] ?>" name="food_items[<?= $item['item_id'] ?>]" 
                                               class="quantity-input" value="0" min="0" max="10" onchange="updateFoodTotal()">
                                        <button type="button" class="quantity-btn" onclick="increaseQuantity(<?= $item['item_id'] ?>)">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="food-summary">
                    <h4>Food & Drinks Summary</h4>
                    <div id="foodItemsSummary">
                        <p class="no-food-selected">No food items selected</p>
                    </div>
                    <div class="food-summary-total">
                        <span>Food & Drinks Total:</span>
                        <span id="foodTotal">₦0.00</span>
                    </div>
                </div>
                
                <form id="foodForm" method="post">
                    <input type="hidden" name="selected_seats" value="<?= htmlspecialchars(json_encode($selected_seats)) ?>">
                    <input type="hidden" name="food_items_json" id="foodItemsJson" value="">
                    
                    <button type="submit" class="btn" id="proceedToGuestBtn">
                        Proceed to Guest Information <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="button" class="btn skip-food-btn" onclick="skipFoodSelection()">
                        <i class="fas fa-forward"></i> Skip Food & Drinks
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='booking.php?showtime_id=<?= $showtime_id ?>'">
                        <i class="fas fa-arrow-left"></i> Change Seats
                    </button>
                </form>
            </div>
        </div>

        <!-- Guest Information Section -->
        <div id="guestInfoSection" class="section-container <?= $current_step === 'guest_info' ? 'active' : '' ?>">
            <div class="booking-summary">
                <h3><i class="fas fa-ticket-alt"></i> Booking Summary</h3>
                <div class="selected-seats">
                    <strong>Selected Seats:</strong> <?= implode(', ', $selected_seats) ?>
                </div>
                <?php if ($food_selected && $food_subtotal > 0): ?>
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
                        <span>₦<?= number_format($tickets_subtotal, 2) ?></span>
                    </div>
                    <?php if ($food_selected && $food_subtotal > 0): ?>
                    <div class="summary-item">
                        <span>Food & Drinks:</span>
                        <span>₦<?= number_format($food_subtotal, 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span>Service Fee:</span>
                        <span>₦<?= number_format($service_fee, 2) ?></span>
                    </div>
                    <div class="total-price">
                        <span>Total:</span>
                        <span>₦<?= number_format($total_price, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="guest-info-form">
                <h3><i class="fas fa-user-circle"></i> Guest Information</h3>
                <p>Please provide your contact details for ticket delivery</p>
                <form id="guestInfoForm" action="payment.php" method="post">
                    <input type="hidden" name="showtime_id" value="<?= $showtime_id ?>">
                    <input type="hidden" name="selected_seats" value="<?= htmlspecialchars(json_encode($selected_seats)) ?>">
                    <input type="hidden" name="food_items" value="<?= htmlspecialchars(json_encode($food_items_data)) ?>">
                    <input type="hidden" name="total_price" value="<?= $total_price ?>">
                    <input type="hidden" name="tickets_subtotal" value="<?= $tickets_subtotal ?>">
                    <input type="hidden" name="food_subtotal" value="<?= $food_subtotal ?>">
                    <input type="hidden" name="service_fee" value="<?= $service_fee ?>">
                    <input type="hidden" name="cinema_id" value="<?= $showtime['cinema_id'] ?>">
                    <input type="hidden" name="auditorium_id" value="<?= $showtime['auditorium_id'] ?>">
                    <input type="hidden" name="auditorium_name" value="<?= htmlspecialchars($showtime['auditorium']) ?>">
                    <input type="hidden" name="movie_title" value="<?= htmlspecialchars($showtime['title']) ?>">
                    <input type="hidden" name="showtime_datetime" value="<?= $showtime['start_time'] ?>">
                    
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn">
                        Proceed to Payment <i class="fas fa-credit-card"></i>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='booking.php?showtime_id=<?= $showtime_id ?>&step=food'">
                        <i class="fas fa-arrow-left"></i> Change Food Selection
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const selectedSeats = [];
        const foodItems = {};
        let foodTotal = 0;
        
        function selectSeat(seatElement) {
            const seatId = seatElement.dataset.seat;
            const seatType = seatElement.dataset.type;
            
            if (seatElement.classList.contains('occupied')) return;
            
            if (seatElement.classList.contains('selected')) {
                // Deselect seat
                seatElement.classList.remove('selected');
                selectedSeats.splice(selectedSeats.indexOf(seatId), 1);
            } else {
                // Select seat
                seatElement.classList.add('selected');
                selectedSeats.push(seatId);
            }
            
            updateBookingSummary();
        }
        
        function updateBookingSummary() {
            const displayEl = document.getElementById('selectedSeatsDisplay');
            const inputEl = document.getElementById('selectedSeatsInput');
            const continueBtn = document.getElementById('continueBtn');
            
            if (selectedSeats.length > 0) {
                displayEl.innerHTML = `Selected Seats: <strong>${selectedSeats.join(', ')}</strong>`;
                inputEl.value = JSON.stringify(selectedSeats);
                continueBtn.disabled = false;
            } else {
                displayEl.innerHTML = "No seats selected yet";
                inputEl.value = "";
                continueBtn.disabled = true;
            }
        }
        
        // Food & Drinks functionality
        function increaseQuantity(itemId) {
            const input = document.getElementById(`food_quantity_${itemId}`);
            const currentValue = parseInt(input.value);
            if (currentValue < 10) {
                input.value = currentValue + 1;
                updateFoodItemState(itemId, currentValue + 1);
                updateFoodTotal();
            }
        }
        
        function decreaseQuantity(itemId) {
            const input = document.getElementById(`food_quantity_${itemId}`);
            const currentValue = parseInt(input.value);
            if (currentValue > 0) {
                input.value = currentValue - 1;
                updateFoodItemState(itemId, currentValue - 1);
                updateFoodTotal();
            }
        }
        
        function updateFoodItemState(itemId, quantity) {
            const decreaseBtn = document.querySelector(`button[onclick="decreaseQuantity(${itemId})"]`);
            decreaseBtn.disabled = quantity <= 0;
            
            // Update foodItems object
            if (quantity > 0) {
                foodItems[itemId] = quantity;
            } else {
                delete foodItems[itemId];
            }
        }
        
        function updateFoodTotal() {
            // Calculate total and update summary
            let total = 0;
            let summaryHTML = '';
            
            for (const itemId in foodItems) {
                if (foodItems.hasOwnProperty(itemId)) {
                    const quantity = foodItems[itemId];
                    const itemElement = document.querySelector(`.food-item input[name="food_items[${itemId}]"]`);
                    const itemRow = itemElement.closest('.food-item');
                    const itemName = itemRow.querySelector('.food-item-title span:first-child').textContent;
                    const itemPrice = parseFloat(itemRow.querySelector('.food-item-price').textContent.replace('₦', '').replace(',', ''));
                    
                    total += itemPrice * quantity;
                    
                    summaryHTML += `
                        <div class="food-summary-item">
                            <span>${itemName} x${quantity}</span>
                            <span>₦${(itemPrice * quantity).toFixed(2)}</span>
                        </div>
                    `;
                }
            }
            
            foodTotal = total;
            
            if (summaryHTML === '') {
                summaryHTML = '<p class="no-food-selected">No food items selected</p>';
            }
            
            document.getElementById('foodItemsSummary').innerHTML = summaryHTML;
            document.getElementById('foodTotal').textContent = `₦${total.toFixed(2)}`;
            
            // Update hidden field with food items data
            document.getElementById('foodItemsJson').value = JSON.stringify(foodItems);
        }
        
        function skipFoodSelection() {
            // Submit form with empty food items
            document.getElementById('foodItemsJson').value = JSON.stringify({});
            document.getElementById('foodForm').submit();
        }
        
        // Food category filtering
        document.addEventListener('DOMContentLoaded', function() {
            const categoryBtns = document.querySelectorAll('.food-category-btn');
            const foodItems = document.querySelectorAll('.food-item');
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const category = this.dataset.category;
                    
                    foodItems.forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            
            // Initialize food items if coming back from guest info
            <?php if ($current_step === 'food_selection' && !empty($food_items_data)): ?>
                <?php foreach ($food_items_data as $food_id => $quantity): ?>
                    document.getElementById('food_quantity_<?= $food_id ?>').value = <?= $quantity ?>;
                    updateFoodItemState(<?= $food_id ?>, <?= $quantity ?>);
                <?php endforeach; ?>
                updateFoodTotal();
            <?php endif; ?>
        });
    </script>
</body>
</html>