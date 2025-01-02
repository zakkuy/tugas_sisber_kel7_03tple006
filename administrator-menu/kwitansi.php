<?php
ob_start();  // Mulai output buffer

session_start();

// Cek apakah sesi untuk transaksi ada
if (!isset($_SESSION['transaksi'])) {
    // Jika tidak ada, arahkan kembali ke halaman tertentu
    header("Location: e0edd1921396030fbf2845547212d9abe481995ae659fa1c5f8ade977ed190bf.php");
    exit();
}

// Ambil data transaksi dari session
$transaksi = $_SESSION['transaksi'];

// Mengambil data dari session
$no_meteran = $transaksi['no_meteran'];
$nama_pelanggan = $transaksi['nama_pelanggan'];
$nominal = $transaksi['nominal'];
$formattedToken = $transaksi['token'];
$pembayaran = $transaksi['pembayaran'];
$tanggal = date('d F Y', strtotime($transaksi['tanggal'])); // Format tanggal
$invoice_number = $transaksi['invoice_number'];
$created_by = $transaksi['created_by'];
$nominal_real = $transaksi['nominal_real'];
$jumlah_kwh = $transaksi['jumlah_kwh'];
$tarif = $transaksi['id_tarif'];  // Menangani id_tarif dari session
$daya = $transaksi['daya'];      // Menangani daya dari session

$formattedKwh = number_format($jumlah_kwh, 1, ',', '.');

// Tentukan nilai Kwh per rupiah berdasarkan tarif
if ($tarif == 1) {
    $kwhPerRupiah = 1200;
} elseif ($tarif == 2) {
    $kwhPerRupiah = 1500;
} elseif ($tarif == 3) {
    $kwhPerRupiah = 1700;
} elseif ($tarif == 4) {
    $kwhPerRupiah = 3000;
} elseif ($tarif == 5) {
    $kwhPerRupiah = 4500;
} else {
    $kwhPerRupiah = 0;  // Jika tarif tidak dikenali
}

$ppj = $nominal * 0.02;  // Menghitung 2% dari nominal

// Inisialisasi biaya administrasi
$biaya_administrasi = 0;

// Cek apakah metode pembayaran selain "Tunai"
if ($pembayaran != 'TUNAI') {
    $biaya_administrasi = 5000;  // Misalnya biaya administrasi adalah 5000
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/logorumah.jpg">
  <title>Cetak Invoice</title>
  <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f7fafc;
        margin: 0;
        padding: 0;
        overflow: hidden;  /* Menonaktifkan scroll */
    }
    .container {
      background-color: #fff;
      width: 100%;
      max-width: 1080px;
      margin: 10px auto;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 20px 20px 10px 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
      border-bottom: 2px solid #e2e8f0;
      padding-bottom: 10px;
    }
    .header .title {
      font-size: 24px;
      font-weight: bold;
      color: #2d3748;
    }
    .header .close-icon {
      font-size: 24px;
      cursor: pointer;
      color: #2d3748;
    }
    .logo img {
      height: 50px;
      margin-top: 20px;
    }
    .info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 18px;
    }
    .info .left, .info .right {
      width: 48%;
    }
    .info p {
      margin: 5px 0;
    }
    .info .bold {
      font-weight: bold;
    }
    .section-title {
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 24px;
    }
    .details {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .half {
    width: 48%;
    padding: 10px;
    }

    .half p {
    margin: 5px 0;
    }

    .half .bold {
    display: inline-block; /* Membuat label tetap di satu baris */
    width: 200px; /* Tentukan lebar label */
    margin-left: 50px;
    text-align: left;
    }

    .details {
    display: flex;
    justify-content: center; /* Menyusun elemen secara horisontal di tengah */
    align-items: center; /* Menyusun elemen secara vertikal di tengah */
    text-align: left; /* Menjaga teks tetap rata kiri */
    margin-bottom: 20px;
    }

    .details .half {
      width: 48%;
      padding: 10px;
      border-right: 1px solid #e2e8f0;
    }
    .details .half:last-child {
      border-right: none;
    }
    .details p {
      margin: 5px 0;
    }
    .token {
      text-align: center;
      margin: 1px 0;
      padding: 2px;
      background-color: #fff;
      border: 2px solid #000000;
      font-size: 18px;
    }
    .footer {
      display: flex;
      justify-content: space-between;
      margin-top: 15px;
    }
    .footer p {
      margin: 5px 0;
    }
    .footer .admin {
      font-weight: bold;
    }
    .footer-text {
      text-align: center;
      font-size: 12px;
      color: #a0aec0;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="title">NexLit Invoice</div>
      <div class="close-icon"><a href="report.php" style="text-decoration: none; color: black;">×</a></div>
    </div>
    <div class="info">
    <div class="left">
        <img src="img/logo-nexlit.png" alt="">
    </div>
    <div class="right" style="text-align: right; padding-right: 30px; margin-top: 90px;">
        <p><span class="bold">No. Invoice     :</span> <?php echo htmlspecialchars($invoice_number); ?></p>
        <p><span class="bold">Tanggal Invoice :</span> <?php echo htmlspecialchars($tanggal); ?></p>
    </div>
    </div>


    <div class="section-title" style="margin-top:20px; margin-bottom:10px;">INVOICE PEMBELIAN NEXLIT</div>
    
    <div class="details">
    <div class="half">
    <div class="section-title">KETERANGAN METER</div>
        <p><span class="bold">Nomor Meteran</span>: <span class="center-text"><?php echo htmlspecialchars($no_meteran); ?></span></p>
        <p><span class="bold">Nama Pelanggan</span>: <span class="center-text"><?php echo htmlspecialchars($nama_pelanggan); ?></span></p>
        <p><span class="bold">Tarif / Daya</span>: <span class="center-text"><?php echo htmlspecialchars($tarif ?? ''); ?> / <?php echo htmlspecialchars($daya ?? ''); ?>  watt</span></p>
        <p><span class="bold">Pembayaran</span>: <span class="center-text"><?php echo htmlspecialchars($pembayaran); ?></span></p>
        <p><span class="bold">Nominal Pembayaran</span>: <span class="center-text">Rp<?php echo number_format($nominal, 0, ',', '.'); ?></span></p>
    </div>
      <div class="half">
        <div class="section-title">RINCIAN BIAYA</div>
        <p><span class="bold">Administrasi</span>: <span class="center-text">Rp<?php echo number_format($biaya_administrasi, 0, ',', '.'); ?></span></p>
        <p><span class="bold">Kwh / Rupiah</span>: <span class="center-text">Rp<?php echo number_format($kwhPerRupiah, 0, ',', '.'); ?></span></p>
        <p><span class="bold">PPJ (2%)</span>: <span class="center-text">Rp<?php echo number_format($ppj, 0, ',', '.'); ?></span></p>
        <p><span class="bold">Rp Token</span>: <span class="center-text">Rp<?php echo number_format($nominal_real, 0, ',', '.'); ?></span></p>
        <p><span class="bold">Jumlah Kwh</span>: <span class="center-text"><?php echo htmlspecialchars($formattedKwh); ?> kWh</span></p>
      </div>
    </div>

    <div class="token-result" style="text-align:center; font-size: 20px; font-weight: bold; margin-top: 16px;">
        <p>TOKEN RESULT</p>
    </div>

    <div class="token" style="font-size: 24px; font-weight: bold;">
      <p><span class="bold"><span class="center-text"><?php echo htmlspecialchars($formattedToken); ?></span></p>
    </div>

    <div class="footer" style="display: flex; flex-direction: column; align-items: flex-end; text-align: right; margin-right: 25px; margin-bottom: 10px;">
  <div>
    <p>Jakarta, <?php echo htmlspecialchars($tanggal); ?></p>
  </div>
  <br><br><br><br>
  <div>
    <p class="admin" style="margin-right: 75px;"><?php echo htmlspecialchars($created_by); ?></p>
  </div>
</div>



    <div class="footer-text">
      <p>NEXLIT v1.0</p>
    </div>
</body>
</html>

<?php
// Setelah kwitansi dicetak, hapus sesi transaksi
unset($_SESSION['transaksi']);  // Menghapus data transaksi
ob_end_flush();  // Mengakhiri output buffer dan mengirimkan output ke browser
?>
