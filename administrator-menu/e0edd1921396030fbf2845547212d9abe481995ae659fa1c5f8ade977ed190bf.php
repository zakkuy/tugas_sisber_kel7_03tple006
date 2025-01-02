<?php
ob_start(); // Memulai output buffering
session_start();
include "../connection.php"; // Koneksi database
include "function.php"; // Koneksi ke function

// Cek apakah data pelanggan ada di session
if (!isset($_SESSION['confirm_token_data']) || empty($_SESSION['confirm_token_data'])) {
    // Jika tidak ada, arahkan kembali ke halaman belitoken.php
    header("Location: belitoken.php");
    exit();
}

$customer = $_SESSION['confirm_token_data']; // Ambil data pelanggan dari session

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

// Fungsi untuk menghasilkan token
function generateToken($length = 20) {
    $characters = '0123456789'; // Hanya angka untuk token
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $token;
}

// Fungsi untuk format token dengan spasi setiap 4 digit
function formatToken($token) {
    return implode(' ', str_split($token, 4)); // Memecah token menjadi bagian 4 digit dan menggabungkan dengan spasi
}

$formattedToken = ''; // Inisialisasi variabel token dengan string kosong

// Ambil username dari session
$createdBy = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $tipe_pembayaran_id = isset($_POST['tipe_pembayaran']) ? $_POST['tipe_pembayaran'] : '';
    $nominal_id = isset($_POST['nominal']) ? $_POST['nominal'] : '';

    // Validasi tipe pembayaran dan nominal
    if (empty($tipe_pembayaran_id) || empty($nominal_id)) {
        $error = "Harap pilih metode pembayaran dan nominal.";
    } else {
        // Ambil tipe pembayaran dari database berdasarkan ID
        $query_tipe_pembayaran = "SELECT tipe_pembayaran FROM pembayaran WHERE id = ?";
        $stmt = $connection->prepare($query_tipe_pembayaran);
        $stmt->bind_param("i", $tipe_pembayaran_id);
        $stmt->execute();
        $stmt->bind_result($tipe_pembayaran);
        $stmt->fetch();
        $stmt->close();

        if (!$tipe_pembayaran) {
            $error = "Tipe pembayaran tidak valid.";
        }

        // Ambil nominal dari database berdasarkan ID
        $query_nominal = "SELECT nominal FROM nominal WHERE id = ?";
        $stmt = $connection->prepare($query_nominal);
        $stmt->bind_param("i", $nominal_id);
        $stmt->execute();
        $stmt->bind_result($nominal);
        $stmt->fetch();
        $stmt->close();

        if (!$nominal) {
            $error = "Nominal tidak valid.";
        }

        if (empty($error)) {
            // Jika validasi berhasil, lanjutkan dengan proses lainnya
            $nominal_real = $nominal * 0.98;  // Potongan 2%

            // Tentukan biaya administrasi berdasarkan metode pembayaran
            $biaya_administrasi = 0;
            if (in_array($tipe_pembayaran, ['Transfer BCA', 'Transfer Mandiri', 'Transfer BNI', 'Transfer BRI', 'QRIS', 'Transfer Lainnya'])) {
                $biaya_administrasi = 5000;  // Misalnya biaya administrasi transfer adalah 5000
            }

            // Kurangi nominal_real dengan biaya administrasi jika menggunakan transfer
            $nominal_real -= $biaya_administrasi;

            // Menghitung jumlah_kwh berdasarkan tarif
            $id_tarif = $customer['id_tarif'];
            $stmt = $connection->prepare("SELECT * FROM tarif_daya WHERE id_tarif = ?");
            $stmt->bind_param("i", $id_tarif);
            $stmt->execute();
            $result = $stmt->get_result();
            $tarif = $result->fetch_assoc();
            $stmt->close();

            if ($tarif) {
                // Hitung jumlah KWH berdasarkan tarif
                switch ($tarif['nama_tarif']) {
                    case 'Tarif 01':
                        $jumlah_kwh = $nominal_real / 1200;
                        break;
                    case 'Tarif 02':
                        $jumlah_kwh = $nominal_real / 1500;
                        break;
                    case 'Tarif 03':
                        $jumlah_kwh = $nominal_real / 1700;
                        break;
                    case 'Tarif 04':
                        $jumlah_kwh = $nominal_real / 3000;
                        break;
                    case 'Tarif 05':
                        $jumlah_kwh = $nominal_real / 4500;
                        break;
                    default:
                        $jumlah_kwh = 0;
                        break;
                }

                // Generate token dan nomor invoice
                $token = generateToken(20);
                $invoiceNumber = generateInvoiceNumber($connection);

                // Simpan transaksi ke database
                $stmt = $connection->prepare("INSERT INTO transaksi (no_meteran, nama_pelanggan, nominal, nominal_real, jumlah_kwh, token, pembayaran, tanggal, invoice_number, created_by, tarif, daya) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
                $stmt->bind_param("ssddsssssii", 
                    $customer['no_meteran'], 
                    $customer['nama_pelanggan'], 
                    $nominal, 
                    $nominal_real, 
                    $jumlah_kwh, 
                    $token, 
                    $tipe_pembayaran, 
                    $invoiceNumber, 
                    $createdBy, 
                    $customer['id_tarif'], // Menggunakan 'id_tarif'
                    $customer['daya']  // Menggunakan 'daya'
                );

                if ($stmt->execute()) {
                    // Simpan transaksi ke session
                    $_SESSION['transaksi'] = [
                        'no_meteran' => $customer['no_meteran'],
                        'nama_pelanggan' => $customer['nama_pelanggan'],
                        'nominal' => $nominal,
                        'token' => formatToken($token),
                        'pembayaran' => $tipe_pembayaran,
                        'tanggal' => date('Y-m-d H:i:s'),
                        'invoice_number' => $invoiceNumber,
                        'created_by' => $createdBy,
                        'nominal_real' => $nominal_real,
                        'jumlah_kwh' => $jumlah_kwh,
                        'daya' => $customer['daya'], 
                        'id_tarif' => $customer['id_tarif'], 
                    ];

                    // Redirect ke halaman yang diinginkan setelah sukses
                    header("Location: 09a328e875a0aa1594076f4cad9d6db2901209f0d4fd917e05ded94192916c8c.php");
                    exit;
                } else {
                    $error = "Gagal menyimpan transaksi.";
                }
            } else {
                $error = "Tarif tidak ditemukan.";
            }
        }
    }
}

// Inisialisasi pesan error jika ada
$error = isset($error) ? $error : '';
$success = isset($success) ? $success : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                    <form method="POST" action="">
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
                                <input type="text" class="form-control" id="inputno_meteran" value="<?php echo htmlspecialchars($customer['no_meteran']); ?>" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputnama_pelanggan">Nama Pelanggan</label>
                                <input type="text" class="form-control" id="inputnama_pelanggan" value="<?php echo htmlspecialchars($customer['nama_pelanggan']); ?>" disabled>
                            </div>
                        </div>
                        <div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputtarif">Tarif</label>
								<input type="text" class="form-control" id="inputtarif" value="<?php echo htmlspecialchars($customer['id_tarif']); ?>" disabled>
							</div>
							<div class="form-group col-md-6">
								<label for="inputtipe_rumah">Daya</label>
								<input type="text" class="form-control" id="inputKontak" value="<?php echo htmlspecialchars($customer['daya']); ?>" disabled>
							</div>
						</div>
						<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputpembayaran">Pembayaran</label>
							<select name="tipe_pembayaran" class="form-control" id="inputTipePembayaran">
								<option value="" disabled selected>Pilih Tipe Pembayaran</option>
								<?php 
								// Query untuk mengambil data dari tabel pembayaran
								$query_tipe_pembayaran = "SELECT id, tipe_pembayaran FROM pembayaran";
								$result_tipe_pembayaran = $connection->query($query_tipe_pembayaran);

								// Cek apakah ada data di tabel
								if ($result_tipe_pembayaran->num_rows > 0) {
									while ($row = $result_tipe_pembayaran->fetch_assoc()) {
										echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['tipe_pembayaran']) . "</option>";
									}
								} else {
									echo "<option value='' disabled>Tidak ada data</option>";
								}
								?>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label for="inputnominal">Nominal</label>
							<select id="inputnominal" name="nominal" class="form-control">
								<option value="" disabled selected>Pilih Nominal Pembayaran</option>
								<?php 
								// Query untuk mengambil data dari tabel nominal
								$query_nominal = "SELECT id, nominal FROM nominal";
								$result_nominal = $connection->query($query_nominal);

								// Cek apakah ada data di tabel
								if ($result_nominal->num_rows > 0) {
									while ($row = $result_nominal->fetch_assoc()) {
										// Format nominal dengan pemisah ribuan
										$formatted_nominal = number_format($row['nominal'], 0, ',', '.');
										echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($formatted_nominal) . "</option>";
									}
								} else {
									echo "<option value='' disabled>Tidak ada data</option>";
								}
								?>
							</select>
						</div>
						</div>
							<div class="form-group col-md-6" style="display: flex; justify-content: center; align-items: center; width: 100%; margin: 0;">
								<button type="submit" class="btn" style="width: 300px; height: 50px; margin-top: 20px; margin-bottom: 15px;" onclick="convertToInput()">PURCHASE TOKEN</button>
							</div>

							<div class="form-group col-md-6">
								<label for="inputtoken" style="display: block; text-align: center; font-size: 25px;">TOKEN RESULT</label>
								<input type="text" class="form-control" id="inputtoken" style="width: 100%; height: 60px; font-size: 36px; text-align: center; margin: 0 auto;" value="<?php echo $formattedToken; ?>" readonly>
							</div>

							<!-- Tempat untuk input yang akan muncul setelah klik PURCHASE TOKEN -->
							<div class="form-group col-md-6" id="nominalInput" style="display:none;">
								<label for="nominalInputField" style="display: block; text-align: center; font-size: 20px;">Nominal</label>
								<input type="text" class="form-control" id="nominalInputField" readonly style="width: 500px; height: 50px; margin: 10px auto;">
							</div>

							<div class="form-group col-md-6" id="pembayaranInput" style="display:none;">
								<label for="pembayaranInputField" style="display: block; text-align: center; font-size: 20px;">Pembayaran</label>
								<input type="text" class="form-control" id="pembayaranInputField" readonly style="width: 500px; height: 50px; margin: 10px auto;">
							</div>

							<div style="display: flex; justify-content: center; gap: 50px; margin-top: 30px;">
								<button type="button" class="btn" style="width: 200px; height: 50px;" onclick="showLoadingAndRedirect('index.php')">Cancel</button>
								<button type="button" class="btn" style="width: 200px; height: 50px; background-color: gray; color: white;" disabled>Kwitansi Print</button>
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

<script>
    // Fungsi untuk menonaktifkan tombol Purchase Token dan mengaktifkan Kwitansi Print
    function convertToInput() {
        // Mengubah style input menjadi visible
        document.getElementById('nominalInput').style.display = 'block';
        document.getElementById('pembayaranInput').style.display = 'block';
        
        // Mengaktifkan tombol Kwitansi Print setelah Purchase Token ditekan
        document.getElementById('kwitansiButton').disabled = false;
    }

		// Fungsi untuk loading dan redirect (untuk tombol Cancel)
		function showLoadingAndRedirect(url) {
			// Menambahkan efek loading jika diperlukan
			// Kemudian mengarahkan ke halaman lain
			window.location.href = url;
		}
	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>

	<script>
    // Validasi input formulir
		document.querySelector("form").addEventListener("submit", function (e) {
			const pembayaran = document.getElementById("inputpembayaran").value;
			const nominal = document.getElementById("inputnominal").value;

			if (!pembayaran || !nominal) {
				e.preventDefault(); // Mencegah pengiriman formulir
				Swal.fire('Error', 'Harap pilih metode pembayaran dan nominal!', 'error');
			}
		});

		function convertToInput() {
		// Ambil nilai dari select nominal dan pembayaran
		var nominal = document.getElementById("nominal").value;
		var pembayaran = document.getElementById("pembayaran").value;

		// Ganti select menjadi input dengan nilai yang dipilih
		document.getElementById("nominalInputField").value = nominal;
		document.getElementById("pembayaranInputField").value = pembayaran;

		// Sembunyikan select dan tampilkan input
		document.getElementById("nominal").style.display = 'none';
		document.getElementById("pembayaran").style.display = 'none';
		document.getElementById("nominalInput").style.display = 'block';
		document.getElementById("pembayaranInput").style.display = 'block';

		// Ubah tombol menjadi "Confirm Purchase" atau lainnya jika diperlukan
		document.querySelector("button[type='button']").textContent = "Confirm Purchase";
	}
	</script>
</body>
</html>
