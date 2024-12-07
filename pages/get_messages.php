<?php
session_start();
require_once '../includes/config.php';

// Cek apakah user sudah login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Ambil pesan dari database
$query = "SELECT messages.*, users.username FROM messages JOIN users ON messages.user_id = users.id ORDER BY created_at DESC";
$messages_result = $conn->query($query);

$messages = [];
while ($row = $messages_result->fetch_assoc()) {
    $messages[] = $row;
}

// Kembalikan pesan dalam format JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>
