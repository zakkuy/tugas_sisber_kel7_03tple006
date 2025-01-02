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

// Query untuk mengambil data pelanggan berdasarkan nama pelanggan
$sql = "SELECT * FROM customer WHERE nama_pelanggan = ?"; // Ganti berdasarkan nama_pelanggan
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $nama_pelanggan); // Binding parameter nama_pelanggan
$stmt->execute();
$result = $stmt->get_result();

// Jika data ditemukan, ambil hasilnya
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customer_name = $row['nama_pelanggan'];
    $customer_id = $row['no_meteran'];
    $kontak_pelanggan = $row['kontak_pelanggan'];
    $rukun_tetangga = $row['rukun_tetangga'];
    $rukun_warga = $row['rukun_warga'];
    $kode_pos = $row['kode_pos'];
    $tipe_rumah = $row['tipe_rumah'];
    $tarif = $row['id_tarif'];
    $daya = $row['daya'];
} else {
    echo "Data tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>Profile Pelanggan</title>
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
        border-top: 1px solid #ddd; /* Garis pemisah atas footer */
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

    <script>
        // Menampilkan SweetAlert untuk menyapa pengguna
        Swal.fire({
            title: 'Selamat Datang!',
            text: 'Selamat datang, <?= $nama_pelanggan ?> dengan nomor meteran: <?= $no_meteran ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });

        // Mencegah form untuk submit dengan tombol Enter
        document.getElementById('form-cari').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Mencegah form submit saat menekan Enter
                document.getElementById('submit-button').click(); // Memicu klik tombol submit
            }
        });
    </script>

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
        <a href="#" class="text-center active">
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
    <form id="form-cari" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4 text-center">Profile Pelanggan</h2>

        <div class="flex justify-center items-center mb-6">
            <div class="w-32 h-32">
                <img src="upload/default.png" alt="Foto Pelanggan" class="w-full h-full object-cover rounded-full border">
            </div>
        </div>

        <div class="flex flex-wrap gap-4 gap-y-6">
            <div class="flex-1 sm:w-full md:w-1/2 lg:w-1/3">
                <label for="customer_name" class="block mb-2">Nama Pelanggan</label>
                <input type="text" id="customer_name" name="customer_name" class="w-full p-3 sm:px-4 text-sm border rounded" value="<?= $customer_name ?>" readonly>
            </div>
            <div class="flex-1 sm:w-full md:w-1/2 lg:w-1/3">
                <label for="customer_id" class="block mb-2">Nomor Meteran</label>
                <input type="text" id="customer_id" name="customer_id" class="w-full p-3 sm:px-4 text-sm border rounded" value="<?= $customer_id ?>" readonly>
            </div>
            <div class="flex-1 sm:w-full md:w-1/2 lg:w-1/3">
                <label for="kontak_pelanggan" class="block mb-2">Kontak Pelanggan</label>
                <input type="text" id="kontak_pelanggan" name="kontak_pelanggan" class="w-full p-3 sm:px-4 text-sm border rounded" value="<?= $kontak_pelanggan ?>" readonly>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-4 gap-y-6">
            <div class="flex-1">
                <label for="rukun_tetangga" class="block mb-2">RT</label>
                <input type="text" id="rukun_tetangga" name="rukun_tetangga" class="w-full p-3 border rounded" value="0<?= $rukun_tetangga ?>" readonly>
            </div>
            <div class="flex-1">
                <label for="rukun_warga" class="block mb-2">RW</label>
                <input type="text" id="rukun_warga" name="rukun_warga" class="w-full p-3 border rounded" value="<?= $rukun_warga ?>" readonly>
            </div>
            <div class="flex-1">
                <label for="kode_pos" class="block mb-2">Kode Pos</label>
                <input type="text" id="kode_pos" name="kode_pos" class="w-full p-3 border rounded" value="<?= $kode_pos ?>" readonly>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-4 gap-y-6">
            <div class="flex-1">
                <label for="tipe_rumah" class="block mb-2">Tipe Rumah</label>
                <input type="text" id="tipe_rumah" name="tipe_rumah" class="w-full p-3 border rounded" value="<?= $tipe_rumah ?>" readonly>
            </div>
            <div class="flex-1">
                <label for="tarif" class="block mb-2">Tarif</label>
                <input type="text" id="tarif" name="tarif" class="w-full p-3 border rounded" value="0<?= $tarif ?>" readonly>
            </div>
            <div class="flex-1">
                <label for="daya" class="block mb-2">Daya</label>
                <input type="text" id="daya" name="daya" class="w-full p-3 border rounded" value="<?= $daya . " " . "Watt" ?> " readonly>
            </div>
        </div>

    <!-- Tombol Lanjut -->
        <button type="button" id="back-button" class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600 hover:shadow-lg transition duration-200 ease-in-out mt-6" onclick="window.location.href='pembeliantoken.php'">
            Kembali
        </button>

        <button type="button" id="submit-button" class="bg-orange-500 text-white px-6 py-3 rounded hover:bg-orange-600 hover:shadow-lg transition duration-200 ease-in-out mt-4" onclick="window.location.href='changepassworduser.php'">
            Ganti Password
        </button>

    </form>

    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>
</main>

</body>
</html>
