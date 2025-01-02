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

// Check if the session is set and token exists
if (isset($_SESSION['token'])) {
    // Retrieve token from session
    $token = $_SESSION['token'];

    // Format token with spaces every 4 digits
    $formatted_token = wordwrap($token, 4, ' ', true);

    // Retrieve customer and payment data from session
    $customer = $_SESSION['customer'];
    $payment_id = $_SESSION['payment_id'];
    $nominal = $_SESSION['nominal'];
    $payment_methods = $_SESSION['payment_methods'];

    // Initialize payment details
    $tipe_pembayaran = '';
    $va = '';  // Virtual Account number
    $atasnama = '';  // Payment recipient name

    // Determine the payment method details based on payment_id
    foreach ($payment_methods as $method) {
        if ($method['id'] == $payment_id) {
            $tipe_pembayaran = $method['tipe_pembayaran'];
            // Assign values for Virtual Account and recipient based on payment type
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
                    $qr_image = 'assets/qris.png';  // QRIS does not use a VA number
                    $atasnama = 'PT. NEXT GENERATION ELECTRICITY INTEGRATION';
                    break;
                default:
                    $va = '';
                    break;
            }
        }
    }
} else {
    // Redirect to home page if session token is not found
    echo "Token not found. Please try again.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>Cetak NexLit Token</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Your CSS Styles here */
        body {
            font-family: 'Roboto', sans-serif;
        }
        .virtual-account-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 10px;
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
        .btn-submit {
            background-color: #f97316;
            color: white;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #666;
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

        .btn-submit {
            background-color: #FF7F00; /* Warna biru sebagai latar tombol */
            color: white; /* Warna teks */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease; /* Efek transisi */
        }

        .btn-submit:hover {
            background-color: #FF4500; /* Warna biru gelap saat hover */
            color: #f8f9fa; /* Warna teks berubah sedikit lebih terang */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Efek bayangan */
            transform: scale(1.05); /* Membesar sedikit */
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

        <div class="input-container" style="display: flex; gap: 20px; justify-content: space-between;">
            <!-- Kolom 1 -->
            <div class="input-item" style="flex: 1;">
                <label for="customer_id" class="block mb-2">No. Meter/ID Pelanggan</label>
                <input type="text" id="customer_id" name="customer_id" class="w-full p-3 border rounded" value="<?php echo htmlspecialchars($customer['no_meteran']); ?>" disabled>
            </div>
            <!-- Kolom 2 -->
            <div class="input-item" style="flex: 1;">
                <label for="customer_name" class="block mb-2">Nama Pelanggan</label>
                <input type="text" id="customer_name" name="customer_name" class="w-full p-3 border rounded" value="<?php echo htmlspecialchars($customer['nama_pelanggan']); ?>" disabled>
            </div>
        </div>

        <div class="input-container" style="display: flex; gap: 20px; justify-content: space-between;">
            <!-- Kolom 1 -->
            <div class="input-item" style="flex: 1;">
                <label for="tarif_index" class="block mb-2">Tarif Index</label>
                <input type="text" id="tarif_index" name="tarif_index" class="w-full p-3 border rounded" value="0<?php echo htmlspecialchars($customer['id_tarif']); ?>" disabled>
            </div>
            <!-- Kolom 2 -->
            <div class="input-item" style="flex: 1;">
                <label for="daya" class="block mb-2">Daya</label>
                <input type="text" id="daya" name="daya" class="w-full p-3 border rounded" value="<?php echo htmlspecialchars($customer['daya']) . " " . "Watt" ; ?>" disabled>
            </div>
        </div>


        <div class="input-container" style="display: flex; gap: 20px; justify-content: space-between;">
            <!-- Kolom 1 -->
            <div class="input-item" style="flex: 1;">
                <label for="nominal" class="block mb-2">Nominal</label>
                <input type="text" id="nominal" name="nominal" class="w-full p-3 border rounded" value="Rp<?php echo htmlspecialchars($nominal); ?>" disabled>
            </div>
            <!-- Kolom 2 -->
            <div class="input-item" style="flex: 1;">
                <label for="payment_id" class="block mb-2">Pembayaran</label>
                <input type="text" id="payment_id" name="payment_id" class="w-full p-3 border rounded" value="<?php echo htmlspecialchars($tipe_pembayaran); ?>" disabled>
            </div>
        </div>

        <div class="cetaktoken" style="text-align: center; margin-top: 10px;">
            <p class="token-title" style="font-size: 24px; font-weight: bold;">TOKEN RESULT</p>
            <input 
                type="text" 
                id="cetaktoken" 
                name="cetaktoken" 
                class="token-display w-full p-3 border rounded mb-4" 
                value="<?php echo htmlspecialchars($formatted_token); ?>" 
                disabled 
                style="width: 80%; font-size: 24px; text-align: center; line-height: 36px; color: #333;">
        </div>


        <div class="button-container flex justify-end">
            <button type="button" class="btn btn-cancel" onclick="location.href='pembeliantoken.php'">Selesai</button>
            <button type="button" class="btn btn-submit" onclick="location.href='cetakinvoice.php'">Cetak Invoice</button>
        </div>

        <div class="pengumuman bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-xl font-bold text-orange-500 mb-4">
                <i class="fas fa-info-circle"></i> Informasi Penting
            </h3>
            <ul class="list-disc pl-6">
                <p class="text-gray-700">
                    Tata Cara Penggunaan Token NexLit Klik 
                    <a href="faq.php?form=9" class="text-blue-500 hover:text-blue-700 font-semibold transition-transform duration-300 hover:scale-105">
                        Disini
                    </a>
                </p>
            </ul>
        </div>

    </form>
</main>
    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>
</body>
</html>
