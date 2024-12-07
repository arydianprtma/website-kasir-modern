<?php
session_start();
require_once '../includes/config.php'; // Pastikan jalur ini benar

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses pengiriman pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $user_id = $_SESSION['user_id']; // ID pengguna yang mengirim pesan

    // Insert pesan ke database
    $stmt = $conn->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    // Redirect kembali ke halaman index.php
    header("Location: ../index.php"); // Pastikan jalur ini benar
    exit();
}
?>
