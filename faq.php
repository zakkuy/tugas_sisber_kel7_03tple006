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

        .hidden {
            display: none;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .transform {
            display: inline-block;
            transition: transform 0.3s ease;
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
      <div class="bg-white p-6 rounded shadow-md mb-4">
        <h2 class="text-xl font-bold text-center mb-4">FAQ</h2>
      <!-- Form 1 -->
      <div class="bg-white p-6 rounded shadow-md mb-4" id="form1">
        <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form1-details')">
          <h2 class="text-xl font-bold text-center">Tata Cara Pembelian Token NexLit</h2>
          <span id="icon1" class="text-xl transform transition-transform">▼</span>
        </div>
        <div id="form1-details" class="hidden">
          <form id="form-cari1" action="#" method="POST">
            <p class="mb-4 text-gray-700 leading-relaxed">
              Ingin membeli token listrik secara praktis? Website NexLit bisa menjadi solusi tepat! 
              Simak panduan lengkapnya berikut ini:
            </p>
            <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
              <li>Pastikan koneksi internet stabil.</li>
              <li>Masukkan nomor meteran Anda.</li>
              <li>Pilih nominal pembelian token yang diinginkan, lalu klik <strong>Lanjut</strong>.</li>
              <li>
                Pilih metode pembayaran yang tersedia, lalu klik <strong>Lanjut Pembayaran</strong>.
              </li>
              <li>
                Jika menggunakan metode pembayaran melalui transfer bank:
                <ul class="list-disc ml-6">
                  <li>Salin kode Virtual Account yang tersedia dengan A/n PT. NEXT GENERATION ELECTRICITY INTEGRATION.</li>
                  <li>Klik konfirmasi pembayaran.</li>
                </ul>
              </li>
              <li>
                Jika menggunakan metode pembayaran melalui QRIS, scan QR-CODE yang tersedia.
              </li>
              <li>Lakukan pembayaran, lalu klik <strong>Sudah Bayar</strong>.</li>
              <li>
                Setelah pembayaran terverifikasi, Anda akan mendapatkan kode voucher yang harus Anda isi di meteran listrik.
              </li>
              <li>
                Jika Anda ingin mencetak, klik <strong>Cetak Invoice</strong>. Jika tidak, klik <strong>Selesai</strong>.
              </li>
            </ol>
          </form>
        </div>
      </div>

       <!-- Form 2 -->
      <div class="bg-white p-6 rounded shadow-md mb-4" id="form2">
        <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form2-details')">
          <h2 class="text-xl font-bold text-center">Perhitungan Jumlah Pembelian kWh</h2>
          <span id="icon2" class="text-xl transform transition-transform">▼</span>
        </div>
        <div id="form2-details" class="hidden">
          <form id="form-cari2">
            <p class="mb-4 text-gray-700 leading-relaxed">
              Setiap nominal token menghasilkan jumlah daya listrik dalam satuan kWh, di mana harga token listrik sebanding 
              dengan jumlah daya listrik yang Anda dapatkan. Berikut adalah daftar harga dan konversi token listrik di NexLit:
            </p>
            <ul class="list-disc ml-6 mb-4 text-gray-700 leading-relaxed">
              <li><strong>Rp 50.000</strong>: 36,6 kWh</li>
              <li><strong>Rp 100.000</strong>: 77,5 kWh</li>
              <li><strong>Rp 200.000</strong>: 159,17 kWh</li>
              <li><strong>Rp 500.000</strong>: 404,17 kWh</li>
              <li><strong>Rp 1.000.000</strong>: 812,5 kWh</li>
              <li><strong>Rp 1.500.000</strong>: 1220,83 kWh</li>
              <li><strong>Rp 2.000.000</strong>: 1629,17 kWh</li>
              <li><strong>Rp 5.000.000</strong>: 4079,17 kWh</li>
              <li><strong>Rp 10.000.000</strong>: 8162,5 kWh</li>
            </ul>
            <p class="mb-4 text-gray-700 leading-relaxed">
              <strong>Catatan:</strong> Harga per kWh adalah Rp 1200. Harga di atas sudah termasuk Pajak Penerangan Jalan (PPJ) sebesar 2% 
              dan biaya administrasi sebesar Rp 5.000. Pastikan untuk memilih nominal token listrik yang sesuai dengan kebutuhan Anda.
            </p>
            <h3 class="text-lg font-bold mb-2 text-gray-800">Cara Perhitungan</h3>
            <p class="text-gray-700 leading-relaxed">
              Contoh perhitungan untuk nominal Rp 50.000:
            </p>
            <ul class="list-disc ml-6 text-gray-700 leading-relaxed">
              <li>Tarif: Rp 50.000</li>
              <li>PPJ: 2% dari Rp 50.000 = Rp 1.000</li>
              <li>Biaya Administrasi: Rp 5.000</li>
              <li>Sisa saldo: Rp 50.000 - Rp 1.000 - Rp 5.000 = Rp 44.000</li>
              <li>Jumlah kWh: Rp 44.000 ÷ 1.200 = 36,6 kWh</li>
            </ul>
          </form>
        </div>
      </div>

      <!-- Form 3 -->
      <div class="bg-white p-6 rounded shadow-md mb-4" id="form3">
        <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form3-details')">
          <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via Transfer BCA</h2>
          <span id="icon3" class="text-xl transform transition-transform">▼</span>
        </div>
        <div id="form3-details" class="hidden">
          <form id="form-cari3">
            <p class="mb-4 text-gray-700 leading-relaxed">
              Berikut adalah langkah-langkah pembayaran menggunakan metode transfer bank BCA:
            </p>
            <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
              <li>
                Salin kode Virtual Account yang tersedia dengan A/n 
                <strong>PT. NEXT GENERATION ELECTRICITY INTEGRATION</strong>.
              </li>
              <li>Buka aplikasi <strong>mobile BCA</strong>.</li>
              <li>Pilih menu <strong>"m-Transfer"</strong>.</li>
              <li>Pilih menu <strong>"BCA Virtual Account"</strong>.</li>
              <li>Masukkan nomor BCA Virtual Account.</li>
              <li>Klik <strong>"Send"</strong>.</li>
              <li>Cek nominal yang muncul.</li>
              <li>Masukkan PIN <strong>m-BCA</strong>.</li>
              <li>Notifikasi transaksi berhasil akan muncul.</li>
            </ol>
          </form>
        </div>
      </div>

      <!-- Form 4 -->
      <div class="bg-white p-6 rounded shadow-md mb-4" id="form4">
        <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form4-details')">
          <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via Transfer MANDIRI</h2>
          <span id="icon4" class="text-xl transform transition-transform">▼</span>
        </div>
        <div id="form4-details" class="hidden">
          <form id="form-cari4">
            <p class="mb-4 text-gray-700 leading-relaxed">
              Berikut adalah langkah-langkah untuk melakukan pembayaran melalui transfer bank Mandiri menggunakan aplikasi 
              <strong>Livin' by Mandiri</strong>:
            </p>
            <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
              <li>Salin kode Virtual Account yang tersedia dengan A/n 
                <strong>PT. NEXT GENERATION ELECTRICITY INTEGRATION</strong>.
              </li>
              <li>Buka aplikasi <strong>Livin' by Mandiri</strong>.</li>
              <li>Login ke akun Anda.</li>
              <li>Pilih menu <strong>"Bayar"</strong>.</li>
              <li>Cari penyedia jasa yang sesuai.</li>
              <li>Klik penyedia jasa tersebut.</li>
              <li>Isi nomor Virtual Account yang telah disalin.</li>
              <li>Pilih <strong>"Lanjutkan"</strong>.</li>
              <li>Cek nominal pembayaran yang muncul.</li>
              <li>Jika sudah benar, klik <strong>"Lanjutkan"</strong>.</li>
              <li>Konfirmasi pembayaran.</li>
              <li>Klik <strong>"Lanjut Bayar"</strong>.</li>
              <li>Masukkan PIN <strong>Livin' by Mandiri</strong>.</li>
            </ol>
          </form>
        </div>
      </div>

     <!-- Form 5 -->
    <div class="bg-white p-6 rounded shadow-md mb-4" id="form5">
      <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form5-details')">
        <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via Transfer BNI</h2>
        <span id="icon5" class="text-xl transform transition-transform">▼</span>
      </div>
      <div id="form5-details" class="hidden">
        <form id="form-cari5">
          <p class="mb-4 text-gray-700 leading-relaxed">
            Berikut adalah langkah-langkah untuk melakukan pembayaran melalui transfer bank BNI menggunakan aplikasi 
            <strong>BNI Mobile Banking</strong>:
          </p>
          <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
            <li>Salin kode Virtual Account yang tersedia dengan A/n 
              <strong>PT. NEXT GENERATION ELECTRICITY INTEGRATION</strong>.
            </li>
            <li>Buka aplikasi <strong>BNI Mobile Banking</strong>.</li>
            <li>Masukkan <strong>user ID</strong> dan <strong>password</strong>.</li>
            <li>Pilih menu <strong>"Transfer"</strong>.</li>
            <li>Pilih menu <strong>"Virtual Account Billing"</strong>.</li>
            <li>Pilih rekening debet yang akan digunakan.</li>
            <li>Masukkan nomor Virtual Account pada menu <strong>"Input Baru"</strong>.</li>
            <li>
              Tagihan yang harus dibayarkan akan muncul pada layar konfirmasi.
            </li>
            <li>Konfirmasi transaksi dan masukkan <strong>Password Transaksi</strong>.</li>
            <li>Pembayaran Anda telah berhasil.</li>
          </ol>
        </form>
      </div>
    </div>

    <!-- Form 6 -->
    <div class="bg-white p-6 rounded shadow-md mb-4" id="form6">
      <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form6-details')">
        <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via Transfer BRI</h2>
        <span id="icon6" class="text-xl transform transition-transform">▼</span>
      </div>
      <div id="form6-details" class="hidden">
        <form id="form-cari6">
          <p class="mb-4 text-gray-700 leading-relaxed">
            Berikut adalah langkah-langkah untuk melakukan pembayaran melalui transfer bank BRI menggunakan aplikasi 
            <strong>BRImo</strong>:
          </p>
          <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
            <li>Salin kode Virtual Account yang tersedia dengan A/n 
              <strong>PT. NEXT GENERATION ELECTRICITY INTEGRATION</strong>.
            </li>
            <li>Login ke aplikasi <strong>BRImo</strong>.</li>
            <li>Pilih menu <strong>"Pembayaran"</strong>.</li>
            <li>Pilih opsi <strong>"BRIVA"</strong>.</li>
            <li>Masukkan nomor Virtual Account.</li>
            <li>Masukkan jumlah pembayaran sesuai nominal tagihan.</li>
            <li>Konfirmasi transaksi dengan memasukkan PIN Anda.</li>
            <li>Transaksi berhasil, dan Anda akan menerima notifikasi.</li>
          </ol>
        </form>
      </div>
    </div>

    <!-- Form 7 -->
    <div class="bg-white p-6 rounded shadow-md mb-4" id="form7">
      <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form7-details')">
        <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via Transfer Bank Lainnya</h2>
        <span id="icon7" class="text-xl transform transition-transform">▼</span>
      </div>
      <div id="form7-details" class="hidden">
        <form id="form-cari7">
          <p class="mb-4 text-gray-700 leading-relaxed">
            Berikut adalah langkah-langkah untuk melakukan pembayaran melalui transfer bank lainnya:
          </p>
          <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
            <li>Salin kode Virtual Account yang tersedia dengan A/n 
              <strong>PT. NEXT GENERATION ELECTRICITY INTEGRATION</strong>.
            </li>
            <li>Buka aplikasi mobile banking atau internet banking bank Anda.</li>
            <li>Pilih menu <strong>"Transfer Antar Bank"</strong>.</li>
            <li>Masukkan nomor Virtual Account yang telah disalin sebagai tujuan transfer.</li>
            <li>Masukkan nominal pembayaran sesuai dengan tagihan.</li>
            <li>Konfirmasi detail transfer yang muncul di layar Anda.</li>
            <li>Masukkan PIN atau kode OTP untuk menyelesaikan transaksi.</li>
            <li>Transaksi berhasil. Simpan bukti transfer jika diperlukan.</li>
          </ol>
        </form>
      </div>
    </div>


    <!-- Form 8 -->
    <div class="bg-white p-6 rounded shadow-md mb-4" id="form8">
      <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form8-details')">
        <h2 class="text-xl font-bold text-center">Tata Cara Pembayaran Via QRIS</h2>
        <span id="icon8" class="text-xl transform transition-transform">▼</span>
      </div>
      <div id="form8-details" class="hidden">
        <form id="form-cari8">
          <p class="mb-4 text-gray-700 leading-relaxed">
            Berikut adalah langkah-langkah untuk melakukan pembayaran melalui QRIS:
          </p>
          <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
            <li>Pilih metode pembayaran menggunakan QRIS pada halaman pembayaran.</li>
            <li>Scan kode QRIS yang tersedia menggunakan aplikasi mobile banking, dompet digital, atau aplikasi pendukung lainnya.</li>
            <li>Pastikan detail pembayaran (nominal dan penerima) yang muncul di aplikasi sesuai dengan tagihan Anda.</li>
            <li>Konfirmasi pembayaran dengan memasukkan PIN atau melakukan autentikasi biometrik (jika diperlukan).</li>
            <li>Setelah pembayaran berhasil, Anda akan menerima notifikasi di aplikasi.</li>
            <li>Kembali ke halaman pembayaran dan klik tombol <strong>"Konfirmasi Pembayaran"</strong> untuk menyelesaikan proses.</li>
            <li>Pastikan status pembayaran telah terverifikasi. Anda akan menerima kode voucher listrik Anda.</li>
          </ol>
        </form>
      </div>
    </div>


    <!-- Form 9 -->
    <div class="bg-white p-6 rounded shadow-md mb-4" id="form9">
      <div class="flex justify-between items-center cursor-pointer" onclick="toggleForm('form9-details')">
        <h2 class="text-xl font-bold text-center">Tata Cara Penggunaan Token Listrik NexLit</h2>
        <span id="icon9" class="text-xl transform transition-transform">▼</span>
      </div>
      <div id="form9-details" class="hidden">
        <form id="form-cari9">
          <p class="mb-4 text-gray-700 leading-relaxed">
            Berikut adalah langkah-langkah untuk menggunakan token listrik NexLit:
          </p>
          <ol class="list-decimal ml-6 space-y-2 text-gray-700 leading-relaxed">
            <li>Pastikan Anda telah melakukan pembelian token listrik melalui sistem NexLit.</li>
            <li>Salin kode voucher yang diterima setelah pembayaran berhasil.</li>
            <li>Masukkan kode voucher pada meteran listrik Anda.</li>
            <li>Pastikan kode yang dimasukkan benar dan klik tombol <strong>"ENTER"</strong> atau <strong>"Konfirmasi"</strong>.</li>
            <li>Jika kode voucher diterima, saldo listrik akan terisi sesuai dengan nominal yang dibeli.</li>
            <li>Periksa indikator meteran untuk memastikan saldo token sudah bertambah.</li>
            <li>Jika terjadi kesalahan atau masalah, coba masukkan kembali kode voucher atau hubungi layanan pelanggan NexLit.</li>
          </ol>
        </form>
      </div>
    </div>


      <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
      </footer>
    </main>



<script>
    // Toggle form display
    function toggleForm(formId) {
        const formDetails = document.getElementById(formId);
        const formNumber = formId.match(/\d+/)[0]; // Extract form number
        const icon = document.getElementById(`icon${formNumber}`);

        if (formDetails.classList.contains('hidden')) {
            formDetails.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            formDetails.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    // Open specific form based on URL query parameter
    function openFormFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const formToOpen = params.get('form'); // Get the form number
        if (formToOpen) {
            const formId = `form${formToOpen}-details`;
            const formSection = document.getElementById(`form${formToOpen}`);
            
            // Toggle the form to be visible
            toggleForm(formId);
            
            // Scroll to the form section smoothly
            formSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Run on page load
    window.onload = openFormFromQuery;
</script>

</body>
</html>
