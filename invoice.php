<?php
session_start();
include 'connection.php'; // Pastikan koneksi ke database sudah tersedia

// // Query untuk mengambil data username dari tabel users
// $sql = "SELECT username FROM users";
// $result = $connection->query($sql);

// // Cek apakah ada hasil yang ditemukan
// if ($result->num_rows > 0) {
//     // Ambil username pertama dan simpan ke dalam variabel $username
//     $row = $result->fetch_assoc();
//     $username = $row['username']; // Menyimpan username pertama ke dalam variabel $username
// } else {
//     $username = null; // Jika tidak ada data, set sebagai null
// }

// Cek apakah parameter invoice_number ada di URL
if (isset($_GET['invoice_number'])) {
    $invoice_number = $_GET['invoice_number'];

    // Ambil data transaksi berdasarkan invoice_number
    $query = "SELECT * FROM transaksi WHERE invoice_number = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $invoice_number);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah data ditemukan
    if ($result->num_rows > 0) {
        $transaksi = $result->fetch_assoc();
        
        // Ambil data dari transaksi dengan nilai default jika tidak ada
        $no_meteran = $transaksi['no_meteran'] ?? '';
        $nama_pelanggan = $transaksi['nama_pelanggan'] ?? '';
        $nominal = $transaksi['nominal'] ?? 0;
        $token = $transaksi['token'] ?? '';
        $pembayaran = $transaksi['pembayaran'] ?? '';
        $tanggal = date('d F Y', strtotime($transaksi['tanggal'] ?? '')); // Format tanggal
        $created_by = $transaksi['created_by'] ?? '';
        $nominal_real = $transaksi['nominal_real'] ?? 0;
        $jumlah_kwh = $transaksi['jumlah_kwh'] ?? 0;
        $tarif = $transaksi['tarif'] ?? 0;  // Gunakan nilai default jika tidak ada
        $daya = $transaksi['daya'] ?? 0;  // Gunakan nilai default jika tidak ada

        $formattedToken = chunk_split($token, 4, ' ');

        // Format Kwh untuk ditampilkan
        $formattedKwh = number_format($jumlah_kwh, 1, ',', '.');
        
        // Tentukan nilai Kwh per rupiah berdasarkan tarif
        switch ($tarif) {
            case 1:
                $kwhPerRupiah = 1200;
                break;
            case 2:
                $kwhPerRupiah = 1500;
                break;
            case 3:
                $kwhPerRupiah = 1700;
                break;
            case 4:
                $kwhPerRupiah = 3000;
                break;
            case 5:
                $kwhPerRupiah = 4500;
                break;
            default:
                $kwhPerRupiah = 0;
                break;
        }

        // Menghitung PPJ (Pajak Penerangan Jalan)
        $ppj = $nominal * 0.02;

        // Inisialisasi biaya administrasi
        $biaya_administrasi = 0;

        // Cek apakah metode pembayaran selain "Tunai"
        if ($pembayaran != 'TUNAI') {
            $biaya_administrasi = 5000;  // Misalnya biaya administrasi adalah 5000
        }

    } else {
        // Jika tidak ada transaksi dengan invoice_number tersebut
        echo "Invoice tidak ditemukan.";
        exit();
    }
} else {
    // Jika invoice_number tidak ditemukan di URL
    echo "Nomor invoice tidak valid.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/logorumah.jpg">
  <title>Cetak Invoice Ulang</title>
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
      <div class="close-icon"><a href="reportuser.php" style="text-decoration: none; color: black;">×</a></div>
    </div>
    <div class="info">
    <div class="left">
        <img src="administrator-menu/img/logo-nexlit.png" alt="">
    </div>
    <div class="right" style="text-align: right; padding-right: 30px; margin-top: 90px;">
        <p><span class="bold">No. Invoice     :</span> <?php echo htmlspecialchars($invoice_number); ?></p>
        <p><span class="bold">Tanggal Kwitansi:</span> <?php echo htmlspecialchars($tanggal); ?></p>
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
        <p><span class="bold">Nominal Pembayaran</span>: <span class="center-text">Rp<?php echo number_format($nominal, 0, ',', '.'); ?> Rupiah</span></p>
    </div>
      <div class="half">
        <div class="section-title">RINCIAN BIAYA</div>
        <p><span class="bold">Administrasi</span>: <span class="center-text">Rp<?php echo number_format($biaya_administrasi, 0, ',', '.'); ?> Rupiah</span></p>
        <p><span class="bold">Kwh / Rupiah</span>: <span class="center-text">Rp<?php echo number_format($kwhPerRupiah, 0, ',', '.'); ?> Rupiah</span></p>
        <p><span class="bold">PPJ (2%)</span>: <span class="center-text">Rp<?php echo number_format($ppj, 0, ',', '.'); ?> Rupiah</span></p>
        <p><span class="bold">Rp Token</span>: <span class="center-text">Rp<?php echo number_format($nominal_real, 0, ',', '.'); ?> Rupiah</span></p>
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
    <p class="admin" style="margin-right: 75px;">
        <?php 
        echo ($created_by != $username) ? "System" : htmlspecialchars($created_by); 
        ?>
    </p>
</div>
</div>



    <div class="footer-text">
      <p>NEXLIT v1.0</p>
    </div>
</body>
</html>
