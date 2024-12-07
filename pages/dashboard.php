<?php
session_start();
require_once '../includes/config.php';

// Check login and admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$user_id = $_SESSION['user_id'];

// Handling form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($page) {
        case 'tambah_karyawan':
            $name = $_POST['name'];
            $email = $_POST['email'];
            $position = $_POST['position'];
            $salary = $_POST['salary'];
            $hire_date = $_POST['hire_date'];
            $status = $_POST['status'];
            $role = $_POST['role'];  // Ambil role dari form
            $username = $_POST['username']; // Ambil username dari form
            $password = $_POST['password'];  // Ambil password dari form

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke database employees
            $stmt = $conn->prepare("INSERT INTO employees (name, email, position, salary, hire_date, status, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsss", $name, $email, $position, $salary, $hire_date, $status, $role);
            $stmt->execute();

            // Insert ke database users
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            $stmt->execute();

            $success = "Karyawan berhasil ditambahkan!";
            break;

        case 'update_status':
            $employee_id = $_POST['employee_id'];
            $status = $_POST['status'];

            $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $employee_id);
            $success = $stmt->execute() ? "Status karyawan berhasil diperbarui!" : "Gagal memperbarui status karyawan.";
            break;
    }
}

// Ambil pesan dari database
$query = "SELECT messages.*, users.username FROM messages JOIN users ON messages.user_id = users.id ORDER BY created_at DESC";
$messages_result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="?page=dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'tambah_karyawan' ? 'active' : '' ?>" href="?page=tambah_karyawan">
                                <i class="bi bi-person-plus"></i> Tambah Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'karyawan' ? 'active' : '' ?>" href="?page=karyawan">
                                <i class="bi bi-person"></i> Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'pantau_aktivitas' ? 'active' : '' ?>" href="?page=pantau_aktivitas">
                                <i class="bi bi-eye"></i> Pantau Aktivitas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'laporan_penjualan' ? 'active' : '' ?>" href="?page=laporan_penjualan">
                                <i class="bi bi-graph-up"></i> Laporan Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'laporan_keuangan' ? 'active' : '' ?>" href="?page=laporan_keuangan">
                                <i class="bi bi-cash-stack"></i> Laporan Keuangan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php 
                        $titles = [
                            'dashboard' => 'Dashboard Admin',
                            'tambah_karyawan' => 'Tambah Karyawan',
                            'karyawan' => 'Daftar Karyawan',
                            'pantau_aktivitas' => 'Pantau Aktivitas',
                            'laporan_penjualan' => 'Laporan Penjualan',
                            'laporan_keuangan' => 'Laporan Keuangan'
                        ];
                        echo $titles[$page]; 
                        ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="logout.php" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>

                <?php 
                if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; 

                switch($page) {
                    case 'dashboard':
                        ?>
                        <!-- Quick Stats Cards -->
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Karyawan</h5>
                                        <p class="card-text display-4">
                                            <?php 
                                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM employees");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            echo $result->fetch_assoc()['total'];
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Penjualan Bulan Ini</h5>
                                        <p class="card-text display-4">
                                            <?php 
                                            $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total_revenue FROM sales WHERE MONTH(date) = MONTH(CURRENT_DATE())");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $revenue = $result->fetch_assoc()['total_revenue'];
                                            echo 'Rp ' . number_format($revenue, 0, ',', '.');
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h5 class="card-title">Aktivitas Terakhir</h5>
                                        <p class="card-text">
                                            <?php 
                                            $stmt = $conn->prepare("SELECT description FROM activity_log ORDER BY timestamp DESC LIMIT 1");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $activity = $result->fetch_assoc();

                                            // Periksa apakah $activity tidak null
                                            if ($activity && isset($activity['description'])) {
                                                echo htmlspecialchars($activity['description']);
                                            } else {
                                                echo 'Tidak ada aktivitas';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-danger">
                                    <div class="card-body">
                                        <h5 class="card-title">Peringatan</h5>
                                        <p class="card-text">
                                            <?php 
                                            $stmt = $conn->prepare("SELECT COALESCE(COUNT(*), 0) as total FROM warnings");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            echo $result->fetch_assoc()['total'] . ' Peringatan Aktif';
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'tambah_karyawan':
                        ?>
                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="role" required>
                                            <option value="Karyawan">Karyawan</option>
                                            <option value="Admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Posisi</label>
                                        <input type="text" class="form-control" name="position" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gaji</label>
                                        <input type="number" class="form-control" name="salary" step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Bergabung</label>
                                        <input type="date" class="form-control" name="hire_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="Active">Aktif</option>
                                            <option value="Inactive">Tidak Aktif</option>
                                            <option value="Suspended">Ditangguhkan</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
                                </form>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'karyawan':
                        // Tampilkan daftar karyawan
                        $stmt = $conn->prepare("SELECT * FROM employees");
                        $stmt->execute();
                        $employees = $stmt->get_result();
                        ?>
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Posisi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $employees->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $row['id']; ?>">Update Status</button>

                                                <!-- Modal for updating status -->
                                                <div class="modal fade" id="updateStatusModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="updateStatusModalLabel">Update Status Karyawan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="POST">
                                                                    <input type="hidden" name="employee_id" value="<?php echo $row['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Pilih Status</label>
                                                                        <select class="form-select" name="status" required>
                                                                            <option value="Active">Aktif</option>
                                                                            <option value="Inactive">Tidak Aktif</option>
                                                                            <option value="Suspended">Ditangguhkan</option>
                                                                        </select>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'pantau_aktivitas':
                        // Konten Pantau Aktivitas
                        break;

                    case 'laporan_penjualan':
                        // Konten Laporan Penjualan
                        break;

                    case 'laporan_keuangan':
                        // Konten Laporan Keuangan
                        break;
                }
                ?>
                
                <!-- Menampilkan pesan dari karyawan -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Pesan dari Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pengirim</th>
                                    <th>Pesan</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $messages_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $row['id']; ?>">Balas</button>

                                        <!-- Modal for replying to messages -->
                                        <div class="modal fade" id="replyModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="replyModalLabel">Balas Pesan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="send_reply.php">
                                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Balasan</label>
                                                                <textarea class="form-control" name="message" rows="3" required></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Kirim Balasan</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
