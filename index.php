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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashedPassword = hash('sha256', $password);

    // Query login berdasarkan username
    $query = $connection->prepare("SELECT * FROM user_account WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['password'] === $hashedPassword) {
            // Simpan data user ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_pelanggan'] = $user['nama_pelanggan'];
            $_SESSION['no_meteran'] = $user['no_meteran'];

            // Tambahkan welcome message
            $_SESSION['welcome_message'] = "Selamat datang, " . htmlspecialchars($user['nama_pelanggan']);

            // Redirect ke pembeliantoken.php
            header("Location: pembeliantoken.php");
            exit();
        } else {
            $error = 'Username atau Password Salah!';
        }
    } else {
        $error = 'Username atau Password Salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>Login Pelanggan</title>
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
            onclick="location.href='d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php'">
            Admin
        </button>
    </div>
</header>


<main class="bg-gray-100">
    <form id="form-cari" action="index.php" method="POST" class="bg-white p-6 rounded shadow-md w-full sm:w-1/2 md:w-1/3 lg:max-w-4xl mb-20 px-4 mt-10">
        <h2 class="text-xl font-bold mb-4 text-center">Log In Pelanggan</h2>
        <!-- Display error messages -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Input Username -->
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">Username / Nomor Pelanggan</label>
            <input type="text" id="username" name="username" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Username / Nomor Meteran">
        </div>

        <!-- Input Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 w-full sm:w-4/5 mx-auto">Password</label>
            <input type="password" id="password" name="password" class="w-full sm:w-4/5 p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 mx-auto block" placeholder="Masukkan Password">
        </div>

        <!-- Button Login -->
        <div class="mb-4">
            <button type="submit" class="w-full sm:w-4/5 bg-orange-500 text-white p-2 rounded-md hover:bg-orange-600 focus:outline-none mx-auto block">Login</button>
        </div>

        <div class="forgot-password text-right sm:w-4/5 mx-auto">
            <a href="lupapassword.php" class="text-blue-500 text-sm hover:underline" style="display: inline-block; width: 70%;">Lupa Password?</a>
        </div>
    </form>

</main>

<footer class="bg-[#0d171b] py-6">
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



<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('form-cari').addEventListener('submit', function(event) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username || !password) {
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Username atau Password tidak boleh kosong!',
            confirmButtonColor: '#f6ad55'
        });
    }
});
</script>


</body>
</html>
