<?php
include 'connection.php';
session_start();

// Atur zona waktu
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk mendapatkan IP pengunjung
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$error = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cek jika key 'username' dan 'nama_pelanggan' ada di POST data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $nama_pelanggan = isset($_POST['nama_pelanggan']) ? $_POST['nama_pelanggan'] : '';
    $password_new = isset($_POST['password_new']) ? $_POST['password_new'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validasi input
    if (empty($username) || empty($nama_pelanggan) || empty($password_new) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi!";
    } elseif ($password_new !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Query untuk mencari user berdasarkan username dan nama pelanggan
        $query = $connection->prepare("SELECT * FROM user_account WHERE username = ? AND nama_pelanggan = ?");
        $query->bind_param("ss", $username, $nama_pelanggan);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Hash password baru
            $hashedPassword = hash('sha256', $password_new);

            // Perbarui password jika pengguna ditemukan
            $updateQuery = $connection->prepare("UPDATE user_account SET password = ? WHERE username = ?");
            $updateQuery->bind_param("ss", $hashedPassword, $username);
            if ($updateQuery->execute()) {
                $message = "Password berhasil diperbarui!";
                // Redirect dengan alert setelah 5 detik
                header("refresh:5;url=index.php");
            } else {
                $error = "Gagal memperbarui password!";
            }
        } else {
            $error = 'Username atau Nama Pelanggan tidak ditemukan!';
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
    <title>Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Minimal setinggi layar */
        }

        main {
            flex-grow: 1; /* Isi ruang kosong antara header dan footer */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f9f9f9;
        }

        footer {
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: auto;
            padding: 10px;
            font-size: 14px;
            color: #fff;
            background-color: #0d171b;
            text-align: center;
        }

        .swal2-container {
            z-index: 1050;
            position: fixed; /* Tidak memengaruhi tata letak */
        }
    </style>
</head>
<body class="bg-gray-100">

<header class="bg-[#0d171b] p-4 flex flex-wrap items-center justify-between">
    <!-- Bagian logo dan tagline -->
    <div class="flex items-center mb-2 sm:mb-0">
        <img src="assets/logorumah.jpg" alt="NexLit logo" class="mr-2 w-10 h-10">
        <div>
            <span class="text-white text-lg font-bold">NexLit</span>
            <span class="text-white block sm:inline ml-0 sm:ml-2 text-sm sm:text-base">| Power Up Your Life with NEXLIT</span>
        </div>
    </div>

    <!-- Tombol login -->
    <div>
        <button 
            class="text-white bg-[#1e2a30] px-4 py-2 rounded hover:bg-[#27353b] transition"
            onclick="location.href='index.php'">
            Login
        </button>
    </div>
</header>

<main class="bg-gray-100">
    <form id="form-cari" action="" method="POST" class="bg-white p-6 rounded shadow-md w-full sm:w-1/2 md:w-1/3 lg:max-w-4xl mb-20 px-4 mt-10">
        <h2 class="text-xl font-bold mb-4 text-center">Lupa Password</h2>
        
        <!-- Display error messages -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Input Username -->
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">Username / Nomor Pelanggan</label>
            <input type="text" id="username" name="username" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Username / Nomor Meteran" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" />
        </div>

        <!-- Input Nama Pelanggan -->
        <div class="mb-4">
            <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">Nama Pelanggan</label>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Nama Lengkap" style="text-transform: none;" oninput="this.value = this.value.toUpperCase();" value="<?php echo isset($_POST['nama_pelanggan']) ? htmlspecialchars($_POST['nama_pelanggan'], ENT_QUOTES, 'UTF-8') : ''; ?>" />
        </div>

        <div class="mb-4">
            <label for="password_new" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">New Password</label>
            <input type="password" id="password_new" name="password_new" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Password Baru" />
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Konfirmasi Password" />
        </div>

        <!-- Button Ganti Password -->
        <div class="mb-4">
            <button type="submit" class="w-full sm:w-4/5 bg-orange-500 text-white p-2 rounded-md hover:bg-orange-600 focus:outline-none mx-auto block">Ganti Password</button>
        </div>
    </form>
</main>

<footer class="bg-[#0d171b] py-1">
    <div class="container mx-auto text-center">
        <!-- Baris 1: Nama & Slogan -->
        <div class="flex items-center justify-center text-gray-300 mb-2">
            <span class="text-sm">
                <strong class="font-bold text-yellow-400">NexLit</strong> &copy; 2024
            </span>
            <span class="text-sm ml-2">| Power Up Your Life with <strong class="text-yellow-400">NEXLIT</strong></span>
        </div>
        <!-- Baris 2: Credit -->
        <div>
            <span class="text-sm text-gray-400">
                Powered by <strong class="text-yellow-400">Kelompok 7 03TPLE006</strong>
            </span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($message): ?>
            let countdown = 5; // Start countdown from 5
            const swalInstance = Swal.fire({
                icon: 'success',
                title: '<?php echo $message; ?>',
                html: 'Akan diarahkan kembali dalam <strong>' + countdown + '</strong> detik.',
                showConfirmButton: false,
                timer: 5000, // Set total timer (5 seconds)
                timerProgressBar: true,
                willClose: () => {
                    clearInterval(timerInterval); // Make sure to clear the interval when SweetAlert is closed
                }
            });

            // Update countdown text every second
            const timerInterval = setInterval(() => {
                countdown--; // Decrement countdown

                // Update the countdown text inside the alert
                swalInstance.update({
                    html: 'Akan diarahkan kembali dalam <strong>' + countdown + '</strong> detik.'
                });

                if (countdown <= 0) {
                    clearInterval(timerInterval); // Stop the interval when countdown reaches 0
                    window.location.href = 'index.php'; // Redirect to index.php
                }
            }, 1000); // Update every 1 second
        <?php endif; ?>
    });
</script>

</body>
</html>
