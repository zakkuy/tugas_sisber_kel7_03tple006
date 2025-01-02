<?php
session_start();
include "../connection.php"; // Koneksi database

// Default foto profil jika tidak tersedia
$profilePicture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'profile/default.png';

// Cek apakah cookie user_session ada
if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login
    exit();
}

// Cek login dan role pengguna
if (!isset($_SESSION['email']) && !isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login
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

// Jika tidak ada data pengguna, arahkan ke halaman lain
if (!$user_data) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php");
    exit();
}

// Query untuk mengambil data tarif
$sql = "SELECT id_tarif, nama_tarif, daya FROM tarif_daya";
$result = $connection->query($sql);

// Inisialisasi pesan error dan sukses
$error = '';
$success = '';

// Menangani submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $noMeteran = isset($_POST['no_meteran']) ? $_POST['no_meteran'] : '';
    $kontak = isset($_POST['kontak_pelanggan']) ? trim($_POST['kontak_pelanggan']) : '';
    $namaPelanggan = isset($_POST['nama_pelanggan']) ? strtoupper($_POST['nama_pelanggan']) : ''; // Konversi ke UPPERCASE
    $tipeRumah = isset($_POST['tipe_rumah']) ? $_POST['tipe_rumah'] : '';
    $rukun_tetangga = isset($_POST['rukun_tetangga']) ? $_POST['rukun_tetangga'] : '';
    $rukun_warga = isset($_POST['rukun_warga']) ? $_POST['rukun_warga'] : '01'; // Default RW
    $kode_pos = isset($_POST['kode_pos']) ? $_POST['kode_pos'] : '15417';       // Default Kode POS
    $idTarif = isset($_POST['id_tarif']) ? $_POST['id_tarif'] : '';
    $daya = isset($_POST['daya']) ? $_POST['daya'] : '';
    $alamat = isset($_POST['alamat']) ? $_POST['alamat'] : '';
    $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : '';

    // Validasi input
    if (strlen($noMeteran) != 12) {
        $error = "No Meteran harus terdiri dari tepat 12 karakter.";
    } elseif (!preg_match('/^[0-9]{12,}$/', $kontak)) {
        $error = "Kontak Pelanggan harus berupa angka dan minimal 12 digit.";
    } elseif (empty($noMeteran) || empty($kontak) || empty($namaPelanggan) || empty($tipeRumah) || empty($idTarif) || empty($daya) || empty($alamat) || empty($rukun_tetangga)) {
        $error = "Semua kolom harus diisi.";
    }

    // Validasi unik untuk no_meteran
    if (empty($error)) {
        $sqlCheck = "SELECT COUNT(*) FROM customer WHERE no_meteran = ?";
        $stmtCheck = $connection->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $noMeteran);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($count > 0) {
            $error = "No Meteran sudah terdaftar, silakan gunakan nomor lain.";
        }
    }

    // Jika tidak ada error, masukkan data ke database
    if (empty($error)) {
        $stmt = $connection->prepare("INSERT INTO customer (no_meteran, nama_pelanggan, kontak_pelanggan, tipe_rumah, id_tarif, daya, alamat, rukun_tetangga, rukun_warga, kode_pos, created_by)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssissssss", $noMeteran, $namaPelanggan, $kontak, $tipeRumah, $idTarif, $daya, $alamat, $rukun_tetangga, $rukun_warga, $kode_pos, $created_by);

        if ($stmt->execute()) {
            $success = "Customer berhasil ditambahkan!";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
// Kosongkan daya setelah proses
    $daya = ''; 
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
	<title>NexLit Tambah Customer</title>
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
			<li>
				<a href="belitoken.php" class="btn" onclick="showLoadingAndRedirect(event, 'belitoken.php')">
					<i class='bx bxs-zap' ></i>
					<span class="text">Token</span>
				</a>
			</li>
			<li class="active">
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
					<h1>Tambah Data Customer</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Add Customer</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="text-decoration-none" href="customer.php">Customer</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
                        <li>
							<a class="text-decoration-none" href="index.php">Dashboard</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  class="active" href="index.php">Home</a>
						</li>
					</ul>
				</div>
			</div>

			<!-- Display success or error messages -->
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

            <div class="table-data">
				<div class="order">
                    
				<form method="POST" action="tambahcustomer.php">
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputno_meteran">No Meteran</label>
							<input type="text" class="form-control" id="inputno_meteran" name="no_meteran" placeholder="Masukkan No Meteran" maxlength="12">
						</div>
						<div class="form-group col-md-6">
							<label for="inputnama_pelanggan">Nama Pelanggan</label>
							<input type="text" class="form-control" id="inputnama_pelanggan" name="nama_pelanggan" placeholder="Masukkan Nama Pelanggan" oninput="this.value = this.value.toUpperCase();" >
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputKontak">Kontak Pelanggan</label>
							<input type="text" class="form-control" id="inputKontak" name="kontak_pelanggan" placeholder="Masukkan Kontak Pelanggan" >
						</div>
						<div class="form-group col-md-6">
							<label for="inputtipe_rumah">Tipe Rumah</label>
							<select name="tipe_rumah" class="form-control" id="inputtipe_rumah">
								<option value="" disabled selected>Tipe Rumah</option>
								<?php 
								// Query untuk mengambil data dari tabel hunian
								$query_tipe_rumah = "SELECT id, tipe_rumah FROM hunian";
								$result_tipe_rumah = $connection->query($query_tipe_rumah);

								// Cek apakah ada data di tabel
								if ($result_tipe_rumah->num_rows > 0) {
									while ($row = $result_tipe_rumah->fetch_assoc()) {
										echo "<option value='" . $row['id'] . "'>" . $row['tipe_rumah'] . "</option>";
									}
								} else {
									echo "<option value='' disabled>Tidak ada data</option>";
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputrt">RT</label>
							<select name="rukun_tetangga" class="form-control" id="inputrt">
								<option value="" disabled selected>Pilih RT</option>
								<?php 
								// Query untuk mengambil data dari tabel hunian
								$query_rukun_tetangga = "SELECT id, rukun_tetangga FROM rukun_masyarakat";
								$result_rukun_tetangga = $connection->query($query_rukun_tetangga);

								// Cek apakah ada data di tabel
								if ($result_rukun_tetangga->num_rows > 0) {
									while ($row = $result_rukun_tetangga->fetch_assoc()) {
										echo "<option value='" . $row['id'] . "'>" . $row['rukun_tetangga'] . "</option>";
									}
								} else {
									echo "<option value='' disabled>Tidak ada data</option>";
								}
								?>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label for="inputrw">RW</label>
							<input type="text" class="form-control" id="inputrw" name="rukun_warga" value="01" readonly>
						</div>
						<div class="form-group col-md-6">
							<label for="inputkode_pos">Kode POS</label>
							<input type="text" class="form-control" id="inputkode_pos" name="kode_pos" value="15417" readonly>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputtarif_daya">Tarif</label>
							<select class="form-control" id="inputtarif_daya" name="id_tarif">
								<option value="" disabled selected>Pilih Tarif</option>
								<?php
								if ($result->num_rows > 0) {
									while ($row = $result->fetch_assoc()) {
										echo "<option value='{$row['id_tarif']}'>{$row['nama_tarif']}</option>";
									}
								} else {
									echo "<option value=''>Tidak ada data</option>";
								}
								?>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label for="inputDaya">Daya</label>
							<input type="text" class="form-control" id="inputDaya" name="daya" value="<?php echo isset($daya) ? $daya : ''; ?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="customTextarea">Alamat</label>
						<textarea id="customTextarea" name="alamat" rows="3" class="custom-textarea" maxlength="254" oninput="updateCharacterCount()"></textarea>
						<small id="charCount">0/254</small>

						<script>
							// Memastikan hitung karakter diperbarui saat halaman dimuat
							document.addEventListener('DOMContentLoaded', function() {
								updateCharacterCount();
							});

							// Fungsi untuk menghitung karakter
							function updateCharacterCount() {
								var textarea = document.getElementById('customTextarea');
								var charCount = document.getElementById('charCount');
								charCount.textContent = textarea.value.length + '/254';
							}
						</script>
					</div>
					<button type="submit" class="btn">Tambah Data</button>
					<button type="button" class="btn" onclick="showLoadingAndRedirect('customer.php')">Cancel</button>
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
		// Mengosongkan input daya saat tarif dipilih
		document.getElementById('inputtarif_daya').addEventListener('change', function() {
			var selectedId = this.value;

			// Cek apakah ada ID tarif yang dipilih
			if (!selectedId) {
				return;
			}

			var tarifData = [
				{ id: 1, nama_tarif: 'Tarif 01', daya: 900 },
				{ id: 2, nama_tarif: 'Tarif 02', daya: 1300 },
				{ id: 3, nama_tarif: 'Tarif 03', daya: 2200 },
				{ id: 4, nama_tafif: 'Tarif 04', daya: 3500 },
				{ id: 5, nama_tarif: 'Tarif 05', daya: 5500}
			];

			var daya = '';

			// Temukan daya yang sesuai dengan tarif yang dipilih
			for (var i = 0; i < tarifData.length; i++) {
				if (tarifData[i].id == selectedId) {
					daya = tarifData[i].daya;
					break;
				}
			}

			// Set nilai daya di input
			if (daya !== '') {
				document.getElementById('inputDaya').value = daya;
			} else {
				document.getElementById('inputDaya').value = 'Tidak ditemukan';
			}
		});

		// Menangani pengiriman form dan mengosongkan nilai daya setelah form disubmit
		document.querySelector('form').addEventListener('submit', function(event) {
			// Hapus nilai input daya setelah submit
			document.getElementById('inputDaya').value = '';

			// Anda bisa melakukan tambahan logika lain di sini jika diperlukan, misalnya:
			// event.preventDefault(); // Gunakan ini jika Anda ingin menangani submit secara manual
		});
	</script>



	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="script.js"></script>

</body>
</html>
