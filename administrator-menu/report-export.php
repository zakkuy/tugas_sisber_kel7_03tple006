<?php

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=report-transaksi.xls");

session_start();
include "../connection.php"; // Koneksi database

$profilePicture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'profile/default.png';

// Cek jika cookie user_session tidak ada
if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

// Cek jika pengguna tidak login atau bukan admin
if (!isset($_SESSION['email']) && !isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

// Ambil data pengguna dari database
$userId = $_SESSION['user_id']; // Pastikan session ini sudah di-set saat login
$query = "SELECT id, fullname, username, email, role, create_on, profile_picture, about, status FROM users WHERE id = ?";
$stmt = $connection->prepare($query);

if (!$stmt) {
    die("Prepare statement gagal: " . $connection->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Jika tidak ada data yang ditemukan, arahkan ke halaman lain
if (!$user_data) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Misalnya arahkan ke halaman login
    exit();
}

// Ambil data dari database (pastikan query sesuai dengan kolom yang ingin ditampilkan)
$query = "SELECT id, tanggal, nama_pelanggan, no_meteran, nominal, jumlah_kwh, invoice_number, created_by FROM transaksi";  // Contoh query, sesuaikan dengan tabel yang Anda pakai
$result = $connection->query($query);

// Inisialisasi pesan error
$error = '';
$success = '';

$query = "SELECT id, tanggal, nama_pelanggan, no_meteran, nominal, jumlah_kwh, invoice_number, created_by FROM transaksi";

// Inisialisasi ID berurut
$id = 1;

?>
<!DOCTYPE html>
<html lang="en">
<body>

	<!-- SIDEBAR -->
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">

		<!-- MAIN -->
		<main>
			<div class="table-data">
				<div class="order">
					<div style="text-align: center;">
						<h3>Report Transaksi</h3>
					</div>
<table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
    <thead>
        <tr>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">ID</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Tanggal</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Nama Pelanggan</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">No Meteran</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Nominal</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Jumlah KWh</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Invoice Number</th>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Dibuat Oleh</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Format tanggal menjadi d-m-Y
                $formatted_date = date("d-m-Y", strtotime($row['tanggal']));
                // Format nominal dengan titik untuk pemisah ribuan
                $formatted_nominal = "Rp" . number_format($row['nominal'], 0, ',', '.');
                
                // Menggunakan htmlspecialchars untuk menghindari XSS
                $invoice_number = htmlspecialchars($row['invoice_number']);
                $created_by = htmlspecialchars($row['created_by']);
                $nama_pelanggan = htmlspecialchars($row['nama_pelanggan']);
                $no_meteran = htmlspecialchars($row['no_meteran']);
                $jumlah_kwh_raw = $row['jumlah_kwh'];

				// Menghapus titik yang berlebihan setelah dua angka desimal
				$jumlah_kwh = preg_replace('/(\.\d{2})\./', '$1', $jumlah_kwh_raw); 

				// Format Jumlah KWh menjadi dua desimal
				$jumlah_kwh = number_format($jumlah_kwh, 2, '.', ',');

                echo "<tr>";
                // Mengatur ID menjadi angka biasa (1, 2, 3, ...)
                echo "<td style='border: 1px solid black; padding: 8px; text-align: left;'>{$id}</td>";
                echo "<td style='border: 1px solid black; padding: 8px; text-align: left;'>{$formatted_date}</td>";
                echo "<td style='border: 1px solid black; padding: 8px; text-align: left;'>{$nama_pelanggan}</td>";
                // Nomor Meteran tampilkan dengan tanda kutip sekitar nilai
                echo "<td style='border: 1px solid #000; padding: 5px;'>=\"" . htmlspecialchars($row['no_meteran']) . "\"</td>";
                echo "<td style='border: 1px solid black; padding: 8px; text-align: left;'>{$formatted_nominal}</td>";
                echo "<td style='border: 1px solid #000; padding: 5px;'>=\"" . htmlspecialchars($row['jumlah_kwh']) . "\"</td>";
                echo "<td style='border: 1px solid black; padding: 8px; text-align: left;'>{$invoice_number}</td>";
                // Dibuat Oleh tampilkan dengan tanda kutip sekitar nilai
                echo "<td style='border: 1px solid #000; padding: 5px;'>=\"" . htmlspecialchars($row['created_by']) . "\"</td>";
                echo "</tr>";
                $id++; // Increment nomor urut
            }
        } else {
            echo "<tr><td colspan='8' style='border: 1px solid black; padding: 8px; text-align: center;'>No Data Found</td></tr>";
        }
        ?>
    </tbody>
</table>


				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

</body>
</html>
