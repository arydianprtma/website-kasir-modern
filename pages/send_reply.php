<?php
session_start();
require_once '../includes/config.php';

// Cek apakah user sudah login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Proses pengiriman balasan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $user_id = $_POST['user_id']; // ID pengguna yang mengirim pesan

    // Insert balasan ke database
    $stmt = $conn->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    // Redirect kembali ke halaman admin
    header("Location: admin.php?page=karyawan"); // Ganti dengan halaman yang sesuai
    exit();
}
?>
