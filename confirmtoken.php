<?php
ob_start(); // Memulai output buffering
session_start();
include 'connection.php';  // Pastikan koneksi ke database sudah benar
include "administrator-menu/function.php"; // Koneksi ke function

// Cek apakah session sudah ada, jika tidak, redirect ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect ke halaman login
    exit;
}

// Ambil data session pengguna
$username = $_SESSION['username'];
$no_meteran = $_SESSION['no_meteran'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// Cek apakah session sudah ada dan lengkap
if (isset($_SESSION['customer']) && isset($_SESSION['payment_id']) && isset($_SESSION['payment_methods']) && isset($_SESSION['nominal'])) {

    // Generate random token (20 digits)
    $random_token = '';
    for ($i = 0; $i < 20; $i++) {
        $random_token .= mt_rand(0, 9); // Append random digits
    }

    // Save token in session
    $_SESSION['token'] = $random_token;

    // Ambil data dari session
    $customer = $_SESSION['customer'];
    $payment_id = $_SESSION['payment_id'];
    $nominal = $_SESSION['nominal'];
    $payment_methods = $_SESSION['payment_methods'];

    // Tentukan tipe pembayaran berdasarkan payment_id
    $tipe_pembayaran = '';
    $va = ''; // Virtual Account
    $atasnama = '';  // Atas Nama untuk pembayaran
    $qr_image = '';  // Variabel untuk QRIS

    foreach ($payment_methods as $method) {
        if ($method['id'] == $payment_id) {
            $tipe_pembayaran = $method['tipe_pembayaran'];
            // Tentukan nomor Virtual Account dan atas nama berdasarkan tipe pembayaran
            switch ($payment_id) {
                case 1:
                    $va = '502021243035';
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                case 2:
                    $va = '552021244035';
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                case 3:
                    $va = '654565845235';
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                case 4:
                    $va = '857898456523';
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                case 5:
                    $va = '552021243035';
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                case 6:
                    $qr_image = 'assets/qris.png';  // QRIS tidak memerlukan nomor VA
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                default:
                    $va = '';
                    break;
            }
        }
    }
} else {
    echo "Data tidak ditemukan. Silakan coba lagi.";
    exit();
}

// Fungsi untuk format token dengan spasi setiap 4 digit
function formatToken($token) {
    return implode(' ', str_split($token, 4)); // Memecah token menjadi bagian 4 digit dan menggabungkan dengan spasi
}

if (isset($_POST['submitPayment'])) {

    // Mengambil nominal yang sudah dipilih dari session
    $nominal = $_SESSION['nominal'];  // Bisa juga menggunakan $_POST['nominal'] jika berasal dari form

    // Hapus titik sebagai pemisah ribuan dan ubah menjadi integer
    $nominal = str_replace('.', '', $nominal);  // Hapus titik
    $nominal = intval($nominal);  // Ubah menjadi integer

    // Menghitung nominal real
    $nominal_real = $nominal - ($nominal * 0.02) - 5000;  // Menghitung nominal real
    $jumlah_kwh = 0;  // Inisialisasi jumlah KWh

    // Tentukan tarif per KWh berdasarkan id_tarif
    switch ($customer['id_tarif']) {
        case 1:
            $tarif_per_kwh = 1200;  // Tarif untuk id_tarif 01
            break;
        case 2:
            $tarif_per_kwh = 1500;  // Tarif untuk id_tarif 02
            break;
        case 3:
            $tarif_per_kwh = 1700;  // Tarif untuk id_tarif 03
            break;
        case 4:
            $tarif_per_kwh = 3000;  // Tarif untuk id_tarif 04
            break;
        case 5:
            $tarif_per_kwh = 4500;  // Tarif untuk id_tarif 05
            break;
        default:
            $tarif_per_kwh = 0;  // Jika id_tarif tidak valid
            break;
    }

    // Menghitung jumlah KWh
    if ($tarif_per_kwh > 0) {
        $jumlah_kwh = $nominal_real / $tarif_per_kwh;
    }

    // Ambil no_meteran dari session untuk created_by
    $created_by = isset($_SESSION['no_meteran']) ? $_SESSION['no_meteran'] : 'unknown';  // Mengambil no_meteran dari session

    // Generate nomor invoice dengan fungsi yang sudah ada
    $invoice_number = generateInvoiceNumber($connection);

    // Siapkan query untuk menyimpan data transaksi ke tabel 'transaksi'
    $stmt = $connection->prepare("INSERT INTO transaksi (no_meteran, nama_pelanggan, nominal, nominal_real, jumlah_kwh, token, pembayaran, tanggal, invoice_number, created_by, tarif, daya) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");

    // Persiapkan statement dan bind parameter
    if ($stmt) {
        // Bind parameter dengan tipe data yang benar
        $stmt->bind_param("ssddsssssii", 
            $no_meteran, 
            $customer['nama_pelanggan'], 
            $nominal, 
            $nominal_real, 
            $jumlah_kwh, 
            $random_token,  // Tidak perlu diformat lagi, langsung masukkan token yang sudah di-generate
            $tipe_pembayaran, 
            $invoice_number, 
            $username, 
            $customer['id_tarif'], 
            $customer['daya']);

        // Eksekusi query dan periksa jika berhasil
        if ($stmt->execute()) {

            // Simpan transaksi ke session
            $_SESSION['transaksi'] = [
                'no_meteran' => $no_meteran,
                'nama_pelanggan' => $nama_pelanggan,
                'nominal' => $nominal,
                'token' => $random_token,
                'pembayaran' => $tipe_pembayaran,
                'tanggal' => date('Y-m-d H:i:s'),
                'invoice_number' => $invoice_number,
                'created_by' => $created_by,
                'nominal_real' => $nominal_real,
                'jumlah_kwh' => $jumlah_kwh,
                'daya' => $customer['daya'], 
                'id_tarif' => $customer['id_tarif'], 
            ];

            // Redirect ke halaman yang diinginkan setelah sukses
            header("Location: cetaktoken.php");
            exit;
        } else {
            echo "Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.";
        }

        // Tutup statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $connection->error;
    }
}

$link_pembayaran = '';

if ($tipe_pembayaran === 'Transfer BCA') {
    $link_pembayaran = 'faq.php?form=3';
} elseif ($tipe_pembayaran === 'Transfer MANDIRI') {
    $link_pembayaran = 'faq.php?form=4';
} elseif ($tipe_pembayaran === 'Transfer BNI') {
    $link_pembayaran = 'faq.php?form=5';
} elseif ($tipe_pembayaran === 'Transfer BRI') {
    $link_pembayaran = 'faq.php?form=6';
} elseif ($tipe_pembayaran === 'Transfer Lainnya') {
    $link_pembayaran = 'faq.php?form=7';
} elseif ($tipe_pembayaran === 'QRIS') {
    $link_pembayaran = 'faq.php?form=8';
} else {
    $link_pembayaran = 'faq.php'; // Default ke halaman umum jika tipe pembayaran tidak dikenali
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

        .virtual-account-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center; /* Posisi horisontal di tengah */
        justify-content: center; /* Posisi vertikal di tengah */
        text-align: center; /* Memastikan teks di dalam elemen berada di tengah */
        padding: 10px;
        }

        .virtual-account {
        margin-bottom: 10px; /* Jarak antara input dan nama */
        }

        .virtual-account label {
        font-size: 20px; /* Ukuran font label */
        font-weight: bold;
        }

        .virtual-account-input {
        font-size: 26px; /* Ukuran font lebih besar untuk nomor virtual account */
        padding: 15px;
        margin-top: 10px;
        width: 100%;
        text-align: center; /* Menengahkan teks dalam input */
        vertical-align: middle; /* Menengahkan teks secara vertikal */
        }

        .account-name p {
        font-size: 12px; /* Ukuran font untuk nama */
        font-weight: normal;
        margin-top: 10px;
        text-align: center; /* Menengahkan teks dalam <p> */
        line-height: 1.5; /* Mengatur jarak antar baris untuk teks */
        }

        .pengumuman{
            margin: 40px 20px 20px; 
        }

        .button-container {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        }

        .btn {
        font-size: 16px;
        font-weight: bold;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-cancel {
        background-color: #e5e7eb;
        color: #374151;
        }

        .btn-cancel:hover {
        background-color: #d1d5db;
        }

        .btn-submit {
        background-color: #f97316;
        color: white;
        }

        .btn-submit:hover {
        background-color: #ea580c;
        transform: scale(1.05);
        }

        .detail-pembayaran {
            text-align: center;
            margin-top: 20px;
        }

        .gap {
            margin-bottom: 10px;
        }

        .footer {
        text-align: center;
        padding: 10px;
        font-size: 14px;
        color: #666;
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
            <input type="text" id="daya" name="daya" class="w-full p-3 border rounded mb-4" value="<?php echo htmlspecialchars($customer['daya']) . " ". "Watt"; ?>" disabled>
        </div>
    </div>

    <div class="input-container">
        <div class="input-item">
            <label for="nominal" class="block mb-2">Nominal</label>
            <input type="text" id="nominal" name="nominal" class="w-full p-3 border rounded mb-4" value="Rp<?php echo $nominal; ?>" disabled>
        </div>
        <div class="input-item">
            <label for="payment_method" class="block mb-2">Metode Pembayaran</label>
            <input type="text" id="payment_method" name="payment_method" class="w-full p-3 border rounded mb-4" value="<?php echo htmlspecialchars($tipe_pembayaran); ?>" disabled>
        </div>
    </div>

    <div class="detail-pembayaran">
            <!-- Jika bukan QRIS, tampilkan input Virtual Account -->
            <?php if ($payment_id != 6): ?>
                <p class="gap">Virtual Account <?php echo $tipe_pembayaran; ?></p>
                <input type="text" id="virtual_account" name="virtual_account" 
                    class="w-[60%] p-3 border rounded mb-4 text-center text-xl" 
                    value="<?php echo htmlspecialchars($va); ?>" disabled>
                <p>A/n <?php echo htmlspecialchars($atasnama); ?></p>
            <?php endif; ?>

            <!-- Menampilkan QRIS jika metode pembayaran QRIS -->
            <?php if ($payment_id == 6): ?>
                <div class="qr-container">
                    <p>Scan QR Code untuk Pembayaran</p>
                    <img src="<?php echo $qr_image; ?>" alt="QR Code Pembayaran" class="w-[60%] max-w-[200px] mx-auto">
                </div>
                <p>A/n <?php echo htmlspecialchars($atasnama); ?></p>
            <?php endif; ?>
        </div>

    <div class="pengumuman bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-xl font-bold text-orange-500 mb-4">
            <i class="fas fa-exclamation-circle"></i> Penting: Segera Lakukan Pembayaran
        </h3>
        <ul class="list-disc pl-6">
            <p class="text-gray-700 mb-2">
                Segera lakukan pembayaran untuk menghindari pembatalan transaksi.
            </p>
            <p class="text-gray-700">
                Tata Cara Pembayaran Via 
                <span class="font-semibold text-gray-900"><?= $tipe_pembayaran ?></span> Klik 
                <a href="<?= $link_pembayaran ?>" class="text-blue-500 hover:text-blue-700 font-semibold transition-transform duration-300 hover:scale-105">
                    Disini
                </a>
            </p>
        </ul>
    </div>


    <div class="button-container">
        <button type="button" class="btn btn-cancel" onclick="window.location.href = 'pembeliantoken.php';">Batal</button>
        <button type="submit" name="submitPayment" class="btn btn-submit">Konfirmasi Pembayaran</button>
    </div>
    </form>
</main>

    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>

</body>
</html>
