<?php
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anvora Cinemas Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!--<header class="admin-header">
        <h1>Anvora Cinemas Admin</h1>
        <div class="admin-user">
            <span><?= htmlspecialchars($_SESSION['admin_email']) ?></span>
            <a href="logout.php" style="color: white;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>-->