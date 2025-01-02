<?php
session_start();
include "../connection.php"; // Koneksi database
include "function.php"; // Koneksi ke function

// Ambil data pengguna dari session
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

// Ambil data transaksi dari session
$transaksi = $_SESSION['transaksi'] ?? [];

// Pastikan data transaksi tersedia
$no_meteran = $transaksi['no_meteran'] ?? 'N/A';
$nama_pelanggan = $transaksi['nama_pelanggan'] ?? 'N/A';
$nominal = $transaksi['nominal'] ?? 0;
$formattedToken = $transaksi['token'] ?? 'N/A';
$pembayaran = $transaksi['pembayaran'] ?? 'N/A';
$tanggal = isset($transaksi['tanggal']) ? date('d F Y', strtotime($transaksi['tanggal'])) : 'N/A';
$invoice_number = $transaksi['invoice_number'] ?? 'N/A';
$created_by = $transaksi['created_by'] ?? 'N/A';
$nominal_real = $transaksi['nominal_real'] ?? 0;
$jumlah_kwh = $transaksi['jumlah_kwh'] ?? 0;
$tarif = $transaksi['id_tarif'] ?? 0;
$daya = $transaksi['daya'] ?? 0;

// Format nominal KWH
$formattedKwh = number_format($jumlah_kwh, 1, ',', '.');

// Hitung PPJ 2%
$ppj = $nominal * 0.02;

// Tentukan tarif KWh per rupiah
switch ($tarif) {
    case 1: $kwhPerRupiah = 1200; break;
    case 2: $kwhPerRupiah = 1500; break;
    case 3: $kwhPerRupiah = 1700; break;
    case 4: $kwhPerRupiah = 3000; break;
    case 5: $kwhPerRupiah = 4500; break;
    default: $kwhPerRupiah = 0; break;
}

// Biaya administrasi jika tidak "TUNAI"
$biaya_administrasi = ($pembayaran != 'TUNAI') ? 5000 : 0;

// Kurangi biaya administrasi dari nominal_real
$nominal_real -= $biaya_administrasi;

// Inisialisasi pesan error
$error = '';
$success = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Bootsrap -->
	 <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Dashboard</title>
</head>
<body>

	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="index.php" class="brand btn" onclick="showLoadingAndRedirect(event, 'index.php')">
			<i class='bx bxs-like'></i>
			<span class="text">NexLit</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="index.php" class="btn" onclick="showLoadingAndRedirect(event, 'index.php')">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li class="active">
				<a href="belitoken.php" class="btn" onclick="showLoadingAndRedirect(event, 'belitoken.php')">
					<i class='bx bxs-zap' ></i>
					<span class="text">Token</span>
				</a>
			</li>
			<li>
				<a href="customer.php" class="btn" onclick="showLoadingAndRedirect(event, 'customer.php')">
					<i class='bx bxs-user' ></i>
					<span class="text">Data Customer</span>
				</a>
			</li>
			<li>
				<a href="report.php" class="btn" onclick="showLoadingAndRedirect(event, 'report.php')">
					<i class='bx bxs-report'></i>
					<span class="text">Report Transaksi</span>
				</a>
			</li>
			<li>
				<a href="administrator.php" class="btn" onclick="showLoadingAndRedirect(event, 'administrator.php')">
					<i class='bx bxs-user-circle'></i>
					<span class="text">Administrator</span>
				</a>
			</li>
			<li>
				<a href="settings.php" class="btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="logout.php" class="logout btn" onclick="showLoadingAndRedirect(event, 'logout.php')">
					<i class='bx bxs-log-out-circle' ></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu' ></i>
			<form action="#">
			</form>

			<a href="settings.php" class="profile btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
				<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Profile Image">
			</a>

			<a href="logout.php" class="notification">
				<i class='bx bxs-exit' style="color: gray; transition: color 0.3s ease;" onmouseover="this.style.color='red'" onmouseout="this.style.color='gray'"></i>
				<!-- <span class="num">8</span> -->
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Pembelian Token</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Pembelian Token</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="text-decoration-none" href="belitoken.php">Token</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  class="active" href="index.php">Home</a>
						</li>
					</ul>
				</div>
			</div>

            <div class="table-data">
				<div class="order">

                    <form method="POST" action="confirmtoken.php">
						<!-- ALERT -->
							<div class="container mt-4">
								<?php if ($success): ?>
									<div class="alert alert-success">
										<?php echo htmlspecialchars($success); ?>
									</div>
								<?php endif; ?>
								<?php if ($error): ?>
									<div class="alert alert-error">
										<?php echo htmlspecialchars($error); ?>
									</div>
								<?php endif; ?>
							</div>
						<!-- ALERT -->
                        <div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputno_meteran">No Meteran</label>
							<!-- Tampilkan no_meteran dari session transaksi -->
							<input type="text" class="form-control" id="inputno_meteran" value="<?php echo htmlspecialchars($transaksi['no_meteran']); ?>" disabled>
						</div>
						<div class="form-group col-md-6">
							<label for="inputnama_pelanggan">Nama Pelanggan</label>
							<!-- Tampilkan nama_pelanggan dari session transaksi -->
							<input type="text" class="form-control" id="inputnama_pelanggan" value="<?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?>" disabled>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputtarif">Tarif</label>
							<!-- Tampilkan tarif dari session transaksi atau $id_tarif jika diperlukan -->
							<input type="text" class="form-control" id="inputtarif" value="<?php echo htmlspecialchars($transaksi['id_tarif'] ?? ''); ?>" disabled>
						</div>
						<div class="form-group col-md-6">
							<label for="inputtipe_rumah">Daya</label>
							<!-- Tampilkan daya dari session customer -->
							<input type="text" class="form-control" id="inputKontak" value="<?php echo htmlspecialchars($transaksi['daya'] ?? ''); ?>" disabled>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputpembayaran">Pembayaran</label>
							<!-- Tampilkan metode pembayaran dari session transaksi -->
							<input type="text" class="form-control" id="inputpembayaran" value="<?php echo isset($transaksi['pembayaran']) ? htmlspecialchars($transaksi['pembayaran']) : ''; ?>" disabled>
						</div>
						<div class="form-group col-md-6">
							<label for="inputnominal">Nominal</label>
							<!-- Tampilkan nominal dari session transaksi -->
							<input type="text" class="form-control" id="inputnominal" value="<?php echo isset($transaksi['nominal']) ? htmlspecialchars(number_format($transaksi['nominal'], 0, ',', '.')) : ''; ?>" disabled>
						</div>
					</div>

						<div class="form-group col-md-6" style="display: flex; justify-content: center; align-items: center; width:100% margin">
							<button type="submit" class="btn" style="width: 300px; height: 50px; margin-top: 20px; margin-bottom: 15px; background-color: gray; color: white;" disabled>PURCHASE TOKEN</button>
						</div>

						<div class="form-group col-md-6">
							<label for="inputtoken" style="display: block; text-align: center; font-size: 25px;">TOKEN RESULT</label>
							<input type="text" class="form-control" id="inputtoken" style="width: 100%; height: 60px; font-size: 36px; text-align: center; margin: 0 auto;" value="<?php echo $formattedToken; ?>" readonly>
						</div>

						<div style="display: flex; justify-content: center; gap: 50px; margin-top: 30px;">
							<button type="button" class="btn" style="width: 200px; height: 50px;" onclick="showLoadingAndRedirect('index.php')">Kembali</button>
							<button type="button" class="btn" style="width: 200px; height: 50px;" onclick="window.location.href='kwitansi.php'">Invoice Print</button>
						</div>
                    </form>
				</div>
			</div>
            
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<!-- Animasi Loading -->
	 <div class="loading-overlay">
		<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
	 </div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>

</body>
</html>
