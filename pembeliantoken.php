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
$no_meteran = $_SESSION['no_meteran'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// Ambil welcome message dari session jika ada
$welcomeMessage = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : '';
if ($welcomeMessage) {
    unset($_SESSION['welcome_message']); // Hapus pesan agar tidak muncul lagi setelah refresh
}

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_meteran = $_POST['no_meteran'];
    $nominal = $_POST['nominal'];

    // Simpan nilai nominal ke dalam session
    $_SESSION['nominal'] = $nominal;
    
    // Validasi input
    if (empty($no_meteran) || empty($nominal)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nomor meteran dan nominal harus diisi!'
        ]);
        exit();
    }

    // Query untuk cek no_meteran
    $sql = "SELECT * FROM customer WHERE no_meteran = '$no_meteran' LIMIT 1";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Ambil data customer dari database
        $customer = mysqli_fetch_assoc($result);

        // Simpan data customer ke session
        $_SESSION['no_meteran'] = $customer['no_meteran'];
        $_SESSION['customer_name'] = $customer['nama_pelanggan'];
        $_SESSION['tarif_index'] = $customer['id_tarif'];  // Menggunakan ID Tarif
        $_SESSION['daya'] = $customer['daya']; // Daya

        // Jika no_meteran ditemukan, kirimkan response sukses
        echo json_encode([
            'success' => true,
            'message' => 'Nomor meteran ditemukan'
        ]);
    } else {
        // Jika no_meteran tidak ditemukan, kirimkan response error
        echo json_encode([
            'success' => false,
            'message' => 'No Meteran tidak ditemukan!'
        ]);
    }

    exit();
}

// Query untuk mengambil data nominal dari database
$query = "SELECT id, nominal FROM nominal";
$result = mysqli_query($connection, $query);

// Siapkan data nominal dalam array
$nominalList = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nominalList[] = number_format($row['nominal'], 0, ',', '.');
    }
}

// Menambahkan session untuk tipe pembayaran
$paymentMethods = [
    [
        'id' => 1,
        'tipe_pembayaran' => 'Transfer BCA',
        'nama_bank' => 'BCA',
        'va' => '502021243035',
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 2,
        'tipe_pembayaran' => 'Transfer MANDIRI',
        'nama_bank' => 'MANDIRI',
        'va' => '552021244035',
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 3,
        'tipe_pembayaran' => 'Transfer BNI',
        'nama_bank' => 'BNI',
        'va' => '654565845235',
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 4,
        'tipe_pembayaran' => 'Transfer BRI',
        'nama_bank' => 'BRI',
        'va' => '857898456523',
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 5,
        'tipe_pembayaran' => 'Transfer Lainnya',
        'nama_bank' => 'LAINNYA',
        'va' => '552021243035',
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 6,
        'tipe_pembayaran' => 'QRIS',
        'nama_bank' => 'QR',
        'va' => NULL,
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ],
    [
        'id' => 7,
        'tipe_pembayaran' => 'TUNAI',
        'nama_bank' => 'TELLER',
        'va' => NULL,
        'atas_nama' => 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'
    ]
];

// Simpan data metode pembayaran ke dalam session
$_SESSION['payment_methods'] = $paymentMethods;
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
        <a href="#" class="text-center active">
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
    <form id="form-cari" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Beli Token Listrik NexLit</h2>
        <div class="flex items-center mb-4">
            <label class="flex items-center mr-6">
                <input type="radio" name="payment_type" value="token" class="mr-2" checked>
                <span>Token Listrik</span>
            </label>
        </div>
        
        <label for="inputno_meteran" class="block mb-2">No. Meter/ID Pelanggan</label>
        <input type="text" id="inputno_meteran" name="no_meteran" class="w-full p-3 border rounded mb-4" placeholder="No.Meter/ID Pelanggan">

        <!-- Button Grid untuk memilih nominal -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <?php if (!empty($nominalList)): ?>
                <?php foreach ($nominalList as $nominal): ?>
                    <input type="button" 
                        class="nominal-button border p-4 rounded text-center cursor-pointer focus:outline-none" 
                        value="<?php echo htmlspecialchars($nominal); ?>" 
                        aria-label="<?php echo htmlspecialchars($nominal); ?>"
                        onclick="setNominal('<?php echo htmlspecialchars($nominal); ?>', this)">
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada data nominal tersedia.</p>
            <?php endif; ?>
        </div>

        <!-- Input hidden untuk menyimpan nilai nominal -->
        <input type="hidden" id="nominal_input" name="nominal">
        

        <!-- Tombol Lanjut dan Total Harga di Pojok Kanan -->
        <div class="flex justify-end items-center space-x-4">
            <!-- Total Harga -->
            <div class="text-right">
                <span class="text-gray-500">Total Harga</span>
                <div class="mt-2">
                    <span id="selected_nominal" class="font-bold text-red-500">Rp -</span>
                </div>
            </div>

            <!-- Tombol Lanjut -->
            <button type="button" id="submit-button" class="bg-orange-500 text-white px-6 py-3 rounded hover:bg-orange-600 hover:shadow-lg transition duration-200 ease-in-out">
                Lanjut
            </button>
        </div>

        <div class="pengumuman bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-xl font-bold text-orange-500 mb-4">
                <i class="fas fa-bullhorn"></i> Pengumuman
            </h3>
            <ul class="list-disc pl-6">
                <p class="mb-2">
                    Tata Cara Pembelian Token NexLit Klik 
                    <a href="faq.php?form=1" class="text-blue-500 hover:text-blue-700 font-semibold transition duration-200">
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

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Menampilkan alert jika ada pesan -->
    <?php if (!empty($welcomeMessage)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Selamat Datang!',
            text: '<?= ucwords(strtolower($welcomeMessage)) ?> !'
            
        });
    </script>
    <?php endif; ?>

<script>
    // Fungsi untuk mengatur nilai nominal yang dipilih
    function setNominal(nominal, element) {
        document.getElementById('nominal_input').value = nominal;
        document.getElementById('selected_nominal').textContent = 'Rp ' + nominal;

        // Menambahkan border oranye pada tombol yang diklik
        const buttons = document.querySelectorAll('.nominal-button');
        buttons.forEach(button => {
            button.classList.remove('border-orange-500'); // Hapus border oranye pada tombol lain
        });

        element.classList.add('border-orange-500'); // Tambahkan border oranye pada tombol yang diklik
    }

    
    // Fungsi untuk menangani submit
    document.getElementById('submit-button').addEventListener('click', function() {
        const noMeteran = document.getElementById('inputno_meteran').value;
        const nominal = document.getElementById('nominal_input').value;  // Nilai nominal

        if (!noMeteran || !nominal) {
            Swal.fire('Error', 'Nomor meteran dan nominal harus diisi!', 'error');
            return; // Menghentikan eksekusi jika ada input yang kosong
        }

        // Kirim data ke server dengan AJAX (gunakan fetch API)
        fetch('pembeliantoken.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded', // Tipe data POST
            },
            body: new URLSearchParams({
                'no_meteran': noMeteran,
                'nominal': nominal
            })
        })
        .then(response => response.json())  // Parsing JSON dari server
        .then(data => {
            if (data.success) {
                // Redirect ke halaman belitoken.php jika berhasil
                window.location.href = 'belitoken.php?no_meteran=' + encodeURIComponent(noMeteran) + '&nominal=' + encodeURIComponent(nominal);
            } else {
                Swal.fire('Error', data.message || 'Nomor meteran tidak ditemukan!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Terjadi kesalahan pada server!', 'error');
        });
    });
</script>

</body>
</html>
