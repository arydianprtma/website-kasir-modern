<?php
// include database config
include 'includes/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Username sudah terdaftar!";
        $messageType = "error"; // Tipe pesan untuk Toastify
    } else {
        // Validate inputs
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $message = "Password dan Confirm Password tidak cocok!";
            $messageType = "error";
        } else {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                $message = "Registrasi berhasil! Silakan login.";
                $messageType = "success";
            } else {
                $message = "Gagal registrasi: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none; /* Sembunyikan notifikasi secara default */
        }
        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Register</h2>
        
        <?php if ($message): ?>
            <div class="notification <?php echo $messageType; ?>" style="display: block;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="Admin">Admin</option>
            </select>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        
            <button type="submit">Register</button>
        </form>
        <p style="text-align: center;">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</div>

</body>
</html>
