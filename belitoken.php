<?php
session_start();
include 'connection.php';  // Pastikan koneksi sudah benar

// Cek apakah session sudah ada, jika tidak, redirect ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect ke halaman login
    exit;
}

// Ambil data session pengguna
$username = $_SESSION['username'];
$no_meteran = $_SESSION['no_meteran'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// Pastikan nominal ada di session
$nominal = isset($_SESSION['nominal']) ? $_SESSION['nominal'] : '';

// Ambil no_meteran yang dikirimkan dari URL
if (isset($_GET['no_meteran'])) {
    $no_meteran = $_GET['no_meteran'];

    // Query untuk mengambil data pelanggan berdasarkan no_meteran
    $sql = "SELECT * FROM customer WHERE no_meteran = '$no_meteran' LIMIT 1";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Ambil data pelanggan
        $customer = mysqli_fetch_assoc($result);

        // Simpan data pelanggan ke dalam session
        $_SESSION['customer'] = $customer;
        $_SESSION['nominal'] = $nominal;
        $_SESSION['no_meteran'] = $customer['no_meteran'];
        $_SESSION['customer_name'] = $customer['nama_pelanggan'];
        $_SESSION['tarif_index'] = $customer['id_tarif'];  // Menggunakan ID Tarif
        $_SESSION['daya'] = $customer['daya']; // Daya

        // Ambil data metode pembayaran dari database
        $paymentQuery = "SELECT id, tipe_pembayaran FROM pembayaran WHERE id BETWEEN 1 AND 6";
        $paymentResult = mysqli_query($connection, $paymentQuery);

        // Simpan data metode pembayaran ke dalam session
        $_SESSION['payment_methods'] = [];
        while ($row = mysqli_fetch_assoc($paymentResult)) {
            $_SESSION['payment_methods'][] = $row;
        }
    } else {
        echo "Data pelanggan tidak ditemukan!";
        exit();
    }
} else {
    echo "No meteran tidak tersedia!";
    exit();
}

// Logika untuk memilih pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['payment_id']) && !empty($_POST['payment_id'])) {
        // Simpan pilihan pembayaran ke dalam session
        $_SESSION['payment_id'] = $_POST['payment_id'];  // Pastikan payment_id disimpan di session

        
        // Redirect ke halaman konfirmasi
        header('Location: confirmtoken.php');
        exit();
    } else {
        echo "Pilih metode pembayaran terlebih dahulu!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>Pembelian NexLit Token</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .input-container {
            display: flex;
            justify-content: space-between; /* Menyebarkan elemen dengan jarak */
            gap: 16px; /* Menambahkan jarak antar elemen */
        }

        .input-item {
            flex: 1; /* Membuat kedua kolom memiliki lebar yang sama */
        }

        .input-item input {
            width: 100%; /* Menjaga lebar input tetap penuh dalam kolom */
        }

        .pengumuman {
            margin: 40px 20px 20px; 
        }

        /* Container untuk tombol */
        .button-container {
            margin-top: 20px; /* Jarak ke elemen di atasnya */
            display: flex;
            justify-content: flex-end; /* Posisi tombol ke kanan */
            gap: 12px; /* Jarak antar tombol */
        }

        /* Gaya dasar tombol */
        .btn {
            font-size: 16px; /* Ukuran font */
            font-weight: bold; /* Tebal untuk teks */
            padding: 10px 20px; /* Ukuran padding seragam */
            border: none; /* Menghilangkan border default */
            border-radius: 6px; /* Membulatkan sudut tombol */
            cursor: pointer; /* Pointer untuk hover */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Animasi */
        }

        /* Tombol Cancel */
        .btn-cancel {
            background-color: #e5e7eb; /* Abu-abu terang */
            color: #374151; /* Abu-abu gelap untuk teks */
        }

        .btn-cancel:hover {
            background-color: #d1d5db; /* Abu-abu sedikit lebih gelap saat hover */
        }

        /* Tombol Submit */
        .btn-submit {
            background-color: #f97316; /* Oranye terang */
            color: white; /* Teks putih */
        }

        .btn-submit:hover {
            background-color: #ea580c; /* Warna hover lebih gelap */
            transform: scale(1.05); /* Sedikit membesar saat hover */
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
            <a href="pembeliantoken.php" class="text-center active">
                <i class="fas fa-lightbulb text-2xl text-orange-500"></i>
                <p>Token NexLit</p>
            </a>
            <a href="profileuser.php" class="text-center">
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
    <form action="" method="POST" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Beli Token Listrik NexLit</h2>

        <div class="input-container">
            <div class="input-item">
                <label for="customer_id" class="block mb-2">Nomor Meteran</label>
                <input type="text" id="customer_id" name="customer_id" class="w-full p-3 border rounded mb-4" value="<?php echo htmlspecialchars($customer['no_meteran']); ?>" disabled>
            </div>
            <div class="input-item">
                <label for="customer_name" class="block mb-2">Nama Pelanggan</label>
                <input type="text" id="customer_name" name="customer_name" class="w-full p-3 border rounded mb-4" value="<?php echo htmlspecialchars($customer['nama_pelanggan']); ?>" disabled>
            </div>
        </div>

        <div class="input-container">
            <div class="input-item">
                <label for="tarif_index" class="block mb-2">Tarif Index</label>
                <input type="text" id="tarif_index" name="tarif_index" class="w-full p-3 border rounded mb-4" value="0<?php echo htmlspecialchars($customer['id_tarif']); ?>" disabled>
            </div>
            <div class="input-item">
                <label for="daya" class="block mb-2">Daya</label>
                <input type="text" id="daya" name="daya" class="w-full p-3 border rounded mb-4" value="<?php echo htmlspecialchars($customer['daya']) . " ". "Watt" ; ?>" disabled>
            </div>
        </div>

        <div class="input-container">
            <div class="input-item">
                <label for="nominal" class="block mb-2">Nominal</label>
                <input type="text" id="nominal" name="nominal" class="w-full p-3 border rounded mb-4" value="Rp<?php echo htmlspecialchars($nominal); ?>" disabled>
            </div>
            <div class="input-item">
                <label for="payment_id" class="block mb-2">Metode Pembayaran</label>
                <select name="payment_id" id="payment_id" class="w-full p-3 border rounded mb-4">
                    <option value="" disabled selected>Pilih Tipe Pembayaran</option>
                    <?php
                    // Loop untuk menampilkan pilihan pembayaran
                    foreach ($_SESSION['payment_methods'] as $method) {
                        echo "<option value='" . $method['id'] . "'>" . $method['tipe_pembayaran'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="button-container flex justify-end">
            <button type="button" class="btn btn-cancel" onclick="location.href='pembeliantoken.php'">Kembali</button>
            <button type="submit" class="btn btn-submit">Lanjut Pembayaran</button>
        </div>

        <div class="pengumuman bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-xl font-bold text-orange-500 mb-4">
                <i class="fas fa-bullhorn"></i> Pengumuman
            </h3>
            <ul class="list-disc pl-6">
                <p>
                    Perhitungan Jumlah kWh Pembelian Klik 
                    <a href="faq.php?form=2" class="text-blue-500 hover:text-blue-700 font-semibold transition duration-200">
                        Disini
                    </a>
                </p>
            </ul>
        </div>

    </form>

    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("form").on("submit", function (event) {
            event.preventDefault(); // Mencegah submit form default

            let paymentId = $("#payment_id").val(); // Ambil nilai metode pembayaran

            if (!paymentId) {
                // Jika tidak ada metode pembayaran yang dipilih, tampilkan alert
                Swal.fire({
                    icon: 'error',
                    title: 'Metode Pembayaran Kosong!',
                    text: 'Silakan pilih metode pembayaran terlebih dahulu.',
                });
            } else {
                // Jika metode pembayaran sudah dipilih, submit form secara AJAX
                $.ajax({
                    url: $(this).attr("action"), // Ambil URL dari form action
                    type: $(this).attr("method"), // Ambil method form (POST)
                    data: $(this).serialize(), // Serialisasi data form
                    success: function (response) {
                        // Berhasil, redirect ke halaman konfirmasi
                        window.location.href = 'confirmtoken.php';
                    },
                    error: function (xhr, status, error) {
                        // Tampilkan pesan error jika gagal
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan. Silakan coba lagi.',
                        });
                    }
                });
            }
        });
    });
</script>


</body>
</html>
