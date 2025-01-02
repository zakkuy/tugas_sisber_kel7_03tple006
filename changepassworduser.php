<?php
session_start(); // Mulai session
include 'connection.php';

// Cek apakah session sudah ada, jika tidak, redirect ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect ke halaman login
    exit;
}

// Ambil data session pengguna
$username = $_SESSION['username'];
$nama_pelanggan = $_SESSION['nama_pelanggan']; // Ambil nama pelanggan dari session

// Ambil data akun pengguna berdasarkan username di tabel user_account
$sql_account = "SELECT * FROM user_account WHERE username = ?";
$stmt_account = $connection->prepare($sql_account);
$stmt_account->bind_param("s", $username);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

// Jika data akun ditemukan
if ($result_account->num_rows > 0) {
    $account_row = $result_account->fetch_assoc();
    $current_password = $account_row['password']; // Password yang ada di tabel user_account
} else {
    echo "Data akun tidak ditemukan!";
    exit;
}

// Inisialisasi pesan error dan sukses
$error = '';
$success = '';

// Proses jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil password lama, baru, dan konfirmasi password
    $oldPassword = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmNewPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : ''; // Sesuaikan dengan nama input form

    // Validasi form
        if (empty($oldPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $error = 'Semua kolom harus diisi!';
        } elseif ($newPassword !== $confirmNewPassword) {
            $error = 'Password baru dan konfirmasi password tidak cocok!';
        } else {
        // Mengambil username dari sesi
        $username = $_SESSION['username'];
        
        // Hash password
        $hashedOldPassword = hash('sha256', $oldPassword);
        $hashedNewPassword = hash('sha256', $newPassword);

        // Periksa password lama
        $stmt = $connection->prepare("SELECT password FROM user_account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && $user['password'] == $hashedOldPassword) {
            // Update password
            $stmt = $connection->prepare("UPDATE user_account SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashedNewPassword, $username);
            
            if ($stmt->execute()) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Terjadi kesalahan, coba lagi!';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>NexLit Token</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        /* Footer styling */
        .footer {
        text-align: center;
        padding: 10px;
        font-size: 14px;
        color: #666; /* Warna teks footer */
        }

        .footer p {
        margin: 0;
        }

        nav a.active {
            position: relative; /* Membuat elemen relatif agar pseudo-element bisa ditempatkan */
        }

        nav a.active::after {
            content: ''; /* Pseudo-element kosong untuk garis */
            position: absolute;
            bottom: -16px; /* Letakkan garis di bawah elemen */
            left: 0;
            width: 100%; /* Panjang garis mengikuti lebar elemen */
            height: 3px; /* Ketebalan garis */
            background-color: #f97316; /* Warna oranye */
            border-radius: 2px; /* Membulatkan ujung garis */
        }

    </style>
</head>
<body class="bg-gray-100">
<header class="bg-[#0d171b] p-4 flex justify-between items-center">
    <div class="flex items-center">
        <img src="assets/logorumah.jpg" alt="NexLit logo" class="mr-2" width="40" height="40">
        <span class="text-white text-lg font-bold">NexLit</span>
        <span class="text-white ml-2 text-sm sm:text-base md:text-lg lg:text-xl">| Power Up Your Life with NEXLIT</span>
    </div>
    <div class="text-white flex items-center justify-center">
        <!-- Lingkaran dengan huruf pertama nama pelanggan (tampilkan di perangkat kecil) -->
        <div class="sm:hidden flex justify-center items-center w-12 h-12 bg-orange-500 text-white font-bold rounded-full">
            <?= strtoupper(substr($nama_pelanggan, 0, 1)) ?>
        </div>
        <!-- Nama Pelanggan (tampilkan pada perangkat besar) -->
        <p class="hidden sm:block ml-2">Hi, <?= ucwords(strtolower($nama_pelanggan)) ?></p>
    </div>
</header>
<nav class="bg-white shadow-md">
    <div class="container mx-auto flex justify-around py-4">
        <a href="pembeliantoken.php" class="text-center">
            <i class="fas fa-lightbulb text-2xl text-orange-500"></i>
            <p>Token NexLit</p>
        </a>
        <a href="profileuser.php" class="text-center active">
            <i class="fas fa-user text-2xl text-orange-500"></i>
            <p>Profile User</p>
        </a>
        <a href="reportuser.php" class="text-center">
            <i class="fas fa-file text-2xl text-orange-500"></i>
            <p>Report Pembelian</p>
        </a>
        <a href="logout.php" class="text-center">
            <i class="fas fas fa-sign-out-alt text-2xl text-red-500"></i>
            <p>Keluar</p>
        </a>
    </div>
</nav>

<main class="container mx-auto mt-6 lg:max-w-4xl">

    <!-- Pesan Error atau Sukses dengan Tailwind CSS -->
    <?php if ($error): ?>
        <div class="bg-red-500 text-white p-4 rounded mb-4">
            <strong>Error!</strong> <?= $error ?>
        </div>
    <?php elseif ($success): ?>
        <div class="bg-green-500 text-white p-4 rounded mb-4">
            <strong>Success!</strong> <?= $success ?>
        </div>
    <?php endif; ?>

    <form id="form-cari" action="" method="POST" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4 text-center">Profile Pelanggan</h2>

        <div class="flex justify-center items-center mb-6">
            <div class="w-32 h-32">
                <img src="upload/default.png" alt="Foto Pelanggan" class="w-full h-full object-cover rounded-full border">
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <div class="flex-1">
                <label for="old_password" class="block mb-2">Old Password</label>
                <input type="password" id="old_password" name="old_password" class="w-full p-3 border rounded" placeholder="Masukkan Password Lama">
            </div>
            <div class="flex-1">
                <label for="new_password" class="block mb-2">New Password</label>
                <input type="password" id="new_password" name="new_password" class="w-full p-3 border rounded" placeholder="Masukkan Password Baru">
            </div>
            <div class="flex-1">
                <label for="confirm_password" class="block mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full p-3 border rounded" placeholder="Masukkan Confirm Password">
            </div>
        </div>

        <!-- Tombol Lanjut -->
        <button type="button" id="back-button" class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600 hover:shadow-lg transition duration-200 ease-in-out mt-6" onclick="window.location.href='profileuser.php'">
            Kembali
        </button>

        <button type="submit" class="bg-orange-500 text-white px-6 py-3 rounded hover:bg-orange-600 hover:shadow-lg transition duration-200 ease-in-out mt-4">
            Update Password
        </button>
    </form>

    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>
</main>


</body>
</html>
