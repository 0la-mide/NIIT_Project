<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$item_id = (int)$_POST['item_id'];
$name = $_POST['name'];
$price = (float)$_POST['price'];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart or increment quantity
if (isset($_SESSION['cart'][$item_id])) {
    $_SESSION['cart'][$item_id]['quantity'] += 1;
} else {
    $_SESSION['cart'][$item_id] = [
        'name' => $name,
        'price' => $price,
        'quantity' => 1
    ];
}

echo json_encode(['success' => true, 'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))]);