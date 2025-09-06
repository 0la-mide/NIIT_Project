<?php
require_once '../config.php';

$auditorium_id = (int)$_GET['auditorium_id'];

$stmt = $conn->prepare("SELECT seat_map FROM auditoriums WHERE auditorium_id = ?");
$stmt->bind_param("i", $auditorium_id);
$stmt->execute();
$result = $stmt->get_result();
$auditorium = $result->fetch_assoc();

header('Content-Type: application/json');
echo $auditorium['seat_map'];