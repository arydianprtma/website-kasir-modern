<?php
session_start();
require_once 'includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil informasi user yang sudah login
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Tampilkan pesan berdasarkan peran user
$role = $_SESSION['role'];

// Ambil semua pesan (termasuk balasan)
$query_messages = "SELECT messages.*, users.username FROM messages JOIN users ON messages.user_id = users.id WHERE messages.user_id = ? OR messages.user_id IN (SELECT id FROM users WHERE role = 'Admin') ORDER BY created_at DESC";
$stmt_messages = $conn->prepare($query_messages);
$stmt_messages->bind_param("i", $user_id);
$stmt_messages->execute();
$messages_result = $stmt_messages->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="src/logo-poltekkes-Photoroom.png">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 5%;
            max-width: 800px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h3>
            </div>
            <div class="card-body">
                <h5>Dashboard</h5>

                <?php if ($role === 'Admin'): ?>
                    <p>Anda adalah Admin. Anda bisa mengelola data pengguna dan melihat statistik.</p>
                    <a href="manage_users.php" class="btn btn-primary">Kelola Pengguna</a>
                <?php elseif ($role === 'Karyawan'): ?>
                    <p>Anda adalah Karyawan. Anda bisa melihat data pribadi Anda.</p>
                    <a href="view_profile.php" class="btn btn-primary">Lihat Profil</a>
                <?php else: ?>
                    <p>Peran tidak dikenali. Silakan hubungi administrator.</p>
                <?php endif; ?>
                
                <br><br>
                <h5>Pesan</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pengirim</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $messages_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Formulir untuk mengirim pesan -->
                <h5>Kirim Pesan</h5>
                <form method="POST" action="send_message.php">
                    <div class="mb-3">
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>

                <br><br>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
