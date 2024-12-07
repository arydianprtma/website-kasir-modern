<?php
// include database config
include 'includes/config.php';
session_start();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Check username/email and password
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username']; // Simpan username di session

            // Redirect berdasarkan role
            if ($user['role'] === 'Admin') {
                header("Location: pages/dashboard.php");
            } else {
                header("Location: index.php"); // Arahkan karyawan ke index.php
            }
            exit();
        } else {
            $message = "Password salah!";
            $messageType = "error";
        }
    } else {
        $message = "Username atau email tidak ditemukan!";
        $messageType = "error";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Toastify/1.12.0/Toastify.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Toastify/1.12.0/Toastify.min.js"></script>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Login</h2>
        
        <?php if ($message): ?>
            <script>
                // Menampilkan Toast Notification
                Toastify({
                    text: "<?php echo $message; ?>",
                    duration: 3000, // Durasi dalam milidetik
                    close: true,
                    gravity: "top", // Atas
                    position: 'right', // Kanan
                    backgroundColor: "<?php echo $messageType === 'success' ? '#4CAF50' : '#F44336'; ?>", // Hijau untuk sukses, Merah untuk error
                }).showToast();
            </script>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="login">Username/Email:</label>
            <input type="text" name="login" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <label>
                <input type="checkbox" name="remember"> Ingatkan saya
            </label>
            
            <button type="submit">Login</button>
        </form>
        <p><a href="reset_password.php">Lupa Password?</a></p>
        <p style="text-align: center;">Belum punya akun? <a href="register.php">Register</a></p>
    </div>
</div>

</body>
</html>
