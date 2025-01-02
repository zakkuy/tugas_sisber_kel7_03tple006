<?php
session_start();
include "../connection.php"; // Koneksi database

// Cek apakah no_meteran diterima melalui query string
if (isset($_GET['no_meteran'])) {
    $noMeteran = $_GET['no_meteran'];

    // Ambil data pelanggan berdasarkan no_meteran
    $query = "SELECT * FROM customer WHERE no_meteran = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $noMeteran);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc(); // Ambil data pelanggan
    } else {
        echo "Data pelanggan tidak ditemukan.";
        exit();
    }
} else {
    echo "No meteran tidak tersedia.";
    exit();
}

// Cek apakah cookie user_session ada
if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login
    exit();
}

// Cek login dan role pengguna
if (!isset($_SESSION['email'], $_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login
    exit();
}

// Ambil data pengguna dari session
$userId = $_SESSION['user_id']; // Pastikan session ini sudah di-set saat login
$query = "SELECT id, fullname, username, email, role, create_on, profile_picture, about, status FROM users WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Jika tidak ada data pengguna, arahkan ke halaman lain
if (!$user_data) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php");
    exit();
}

// Ambil username dari tabel user_account berdasarkan no_meteran
$queryUserAccount = "SELECT username FROM user_account WHERE no_meteran = ?";
$stmtUserAccount = $connection->prepare($queryUserAccount);
$stmtUserAccount->bind_param("s", $noMeteran); // Bind no_meteran dari customer
$stmtUserAccount->execute();
$resultUserAccount = $stmtUserAccount->get_result();

// Pastikan ada hasil, jika ada ambil username
if ($resultUserAccount->num_rows > 0) {
    $userAccountData = $resultUserAccount->fetch_assoc();
    $usernameAccount = $userAccountData['username'];
} else {
    $usernameAccount = ''; // Jika tidak ditemukan, set sebagai string kosong
}

$stmtUserAccount->close();

// Query untuk mengambil data tarif
$sql = "SELECT id_tarif, nama_tarif, daya FROM tarif_daya";
$result_tarif = $connection->query($sql);

// Menangani submit form (update data pelanggan dan password)
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan hilangkan spasi ekstra
    $kontak = isset($_POST['kontak_pelanggan']) ? htmlspecialchars(trim($_POST['kontak_pelanggan'])) : '';
    $namaPelanggan = isset($_POST['nama_pelanggan']) ? strtoupper(trim($_POST['nama_pelanggan'])) : ''; // Konversi ke UPPERCASE
    $tipe_Rumah = isset($_POST['tipe_rumah']) ? trim($_POST['tipe_rumah']) : '';
    $rukun_tetangga = isset($_POST['rukun_tetangga']) ? trim($_POST['rukun_tetangga']) : '';
    $rukun_warga = isset($_POST['rukun_warga']) ? trim($_POST['rukun_warga']) : '01'; // Default RW
    $kode_pos = isset($_POST['kode_pos']) ? trim($_POST['kode_pos']) : '15417';       // Default Kode POS
    $idTarif = isset($_POST['id_tarif']) ? trim($_POST['id_tarif']) : '';
    $daya = isset($_POST['daya']) ? trim($_POST['daya']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $noMeteranBaru = isset($_POST['no_meteran']) ? trim($_POST['no_meteran']) : ''; // Ambil no_meteran baru dari form

    // Ambil data password baru
    $newPassword = isset($_POST['password_account']) ? trim($_POST['password_account']) : '';

    // Validasi input
    if (strlen($noMeteran) != 12) {
        $error = "No Meteran harus terdiri dari tepat 12 angka.";
    } elseif (strlen($kontak) < 12 || !preg_match('/^[0-9]+$/', $kontak)) {
        $error = "Kontak Pelanggan harus berupa angka dan minimal 12 digit.";
    } elseif (empty($kontak) || empty($namaPelanggan) || empty($tipe_Rumah) || empty($idTarif) || empty($daya) || empty($alamat) || empty($rukun_tetangga)) {
        $error = "Semua kolom harus diisi.";
    }

    // Cek apakah no_meteran baru sudah ada di database
    if (!empty($noMeteranBaru)) {
        $queryCheck = "SELECT * FROM customer WHERE no_meteran = ? AND no_meteran != ?"; 
        $stmtCheck = $connection->prepare($queryCheck);
        $stmtCheck->bind_param("ss", $noMeteranBaru, $noMeteran); // Bind no_meteran baru dan lama
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $error = "No Meteran yang baru sudah terdaftar di database, silakan masukkan nomor meteran yang lain.";
        }
    }

    // Jika tidak ada error, update data pelanggan
    if (empty($error)) {
        // Update data pelanggan ke database
        $sqlUpdate = "UPDATE customer SET 
						nama_pelanggan = ?, 
						kontak_pelanggan = ?, 
						tipe_rumah = ?, 
						id_tarif = ?, 
						daya = ?, 
						alamat = ?, 
						rukun_tetangga = ?, 
						rukun_warga = ?, 
						kode_pos = ? 
                    WHERE no_meteran = ?";
		$stmtUpdate = $connection->prepare($sqlUpdate);
		$stmtUpdate->bind_param("ssssssssss", $namaPelanggan, $kontak, $tipe_Rumah, $idTarif, $daya, $alamat, $rukun_tetangga, $rukun_warga, $kode_pos, $noMeteran);

        if ($stmtUpdate->execute()) {
            $success = "Data pelanggan berhasil diupdate.";
        } else {
            $error = "Terjadi kesalahan saat mengupdate data pelanggan.";
        }

        $stmtUpdate->close();

        // Jika password baru diinputkan, update password
        if (!empty($newPassword)) {
            // Hash password baru menggunakan SHA-256
            $hashedPassword = hash('sha256', $newPassword);

            // Update password pengguna di user_account
            $sqlUpdatePassword = "UPDATE user_account SET password = ? WHERE no_meteran = ?";
            $stmtUpdatePassword = $connection->prepare($sqlUpdatePassword);
            $stmtUpdatePassword->bind_param("ss", $hashedPassword, $noMeteran);

            if ($stmtUpdatePassword->execute()) {
                $success = "Password berhasil diupdate.";
            } else {
                $error = "Terjadi kesalahan saat mengupdate password.";
            }

            $stmtUpdatePassword->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.0/dist/sweetalert2.min.css" rel="stylesheet">
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Update Customer</title>
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
					<h1>Update Data Customer</h1>
					<ul class="breadcrumb">
						<li><a href="#">Update Customer</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="text-decoration-none" href="customer.php">Customer</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="text-decoration-none" href="index.php">Dashboard</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="index.php">Home</a></li>
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
					<div class="alert alert-danger">
						<?php echo htmlspecialchars($error); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="table-data">
				<div class="order">
					<form method="POST" action="">
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputno_meteran">No Meteran</label>
								<input type="text" class="form-control" id="inputno_meteran" name="no_meteran" value="<?= !empty($customer['no_meteran']) ? htmlspecialchars($customer['no_meteran']) : ''; ?>" maxlength="12" disabled>
							</div>
							<div class="form-group col-md-6">
								<label for="inputnama_pelanggan">Nama Pelanggan</label>
								<input type="text" class="form-control" id="inputnama_pelanggan" name="nama_pelanggan" value="<?= !empty($customer['nama_pelanggan']) ? htmlspecialchars($customer['nama_pelanggan']) : ''; ?>" oninput="this.value = this.value.toUpperCase();">
							</div>
						</div>

						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputKontak">Kontak Pelanggan</label>
								<input type="text" class="form-control" id="inputKontak" name="kontak_pelanggan" value="<?= !empty($customer['kontak_pelanggan']) ? htmlspecialchars($customer['kontak_pelanggan']) : ''; ?>">
							</div>
							<div class="form-group col-md-6">
								<label for="inputtipe_rumah">Tipe Rumah</label>
								<select name="tipe_rumah" class="form-control" id="inputtipe_rumah">
									<!-- Default option if no tipe_rumah is selected -->
									<option value="" disabled <?php echo empty($customer['tipe_rumah']) ? 'selected' : ''; ?>>Pilih Tipe Rumah</option>
									
									<?php
									// Query to get the house types from the hunian table
									$query_tipe_rumah = "SELECT id, tipe_rumah FROM hunian";
									$result_tipe_rumah = $connection->query($query_tipe_rumah);

									// Check if there are house types available
									if ($result_tipe_rumah->num_rows > 0) {
										// Loop through all house types
										while ($row = $result_tipe_rumah->fetch_assoc()) {
											// Check if the current house type matches the selected value in $customer['tipe_rumah']
											$selected = ($row['tipe_rumah'] == $customer['tipe_rumah']) ? 'selected' : '';
											// Output the options
											echo "<option value='" . $row['tipe_rumah'] . "' $selected>" . $row['tipe_rumah'] . "</option>";
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
									$query_rukun_tetangga = "SELECT id, rukun_tetangga FROM rukun_masyarakat";
									$result_rukun_tetangga = $connection->query($query_rukun_tetangga);
									if ($result_rukun_tetangga->num_rows > 0) {
										while ($row = $result_rukun_tetangga->fetch_assoc()) {
											$selected = ($row['id'] == $customer['rukun_tetangga']) ? 'selected' : '';
											echo "<option value='" . $row['id'] . "' $selected>" . $row['rukun_tetangga'] . "</option>";
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
									<option value="<?= !empty($customer['id_tarif']) ? $customer['id_tarif'] : ''; ?>">
										<?php 
										if (isset($customer['id_tarif'])) {
											$id_tarif = $customer['id_tarif'];
											$query = "SELECT nama_tarif FROM tarif_daya WHERE id_tarif = '$id_tarif'";
											$result_tarif = mysqli_query($connection, $query);
											if ($result_tarif && $result_tarif->num_rows > 0) {
												$tarif_data = $result_tarif->fetch_assoc();
												echo $tarif_data['nama_tarif']; 
											} else {
												echo 'Pilih tarif';
											}
										} else {
											echo 'Pilih tarif';
										}
										?>
									</option>
									<?php
									// Query untuk menampilkan semua tarif
									$query_tarif = "SELECT id_tarif, nama_tarif FROM tarif_daya";
									$result_tarif = $connection->query($query_tarif);
									if ($result_tarif->num_rows > 0) {
										while ($row = $result_tarif->fetch_assoc()) {
											echo "<option value='" . $row['id_tarif'] . "'>" . $row['nama_tarif'] . "</option>";
										}
									} else {
										echo "<option value=''>Tidak ada data</option>";
									}
									?>
								</select>
							</div>

							<div class="form-group col-md-6">
								<label for="inputDaya">Daya</label>
								<input type="text" class="form-control" id="inputDaya" name="daya" value="<?= !empty($customer['daya']) ? $customer['daya'] : ''; ?>" readonly>
							</div>
						</div>

						<div class="form-group">
							<label for="customTextarea">Alamat</label>
							<textarea id="customTextarea" name="alamat" rows="3" class="custom-textarea" maxlength="254"><?= !empty($customer['alamat']) ? htmlspecialchars($customer['alamat']) : ''; ?></textarea>
							<small id="charCount">0/254</small>
						</div>

						<div class="warning">
							<p>Informasi User Login</p>
						</div>

						<div class="form-row">
							<!-- Field untuk Username -->
							<div class="form-group col-md-6">
								<label for="inputusername_account">Username</label>
								<!-- Username ditampilkan tapi tidak bisa diubah -->
								<input type="text" class="form-control" id="inputusername_account" name="username_account" value="<?= htmlspecialchars($usernameAccount); ?>" maxlength="12" readonly>
							</div>

							<!-- Field untuk Password -->
							<div class="form-group col-md-6">
								<label for="inputpassword_account">Password</label>
								<!-- Input untuk password baru, jika tidak diubah bisa dibiarkan kosong -->
								<input type="password" class="form-control" id="inputpassword_account" name="password_account" placeholder="Masukkan password baru jika ingin mengubah">
							</div>
						</div>

						<button type="submit" class="btn btn-primary" onclick="confirmUpdate()">Update Data</button>
						<button type="button" class="btn btn-primary" onclick="window.location.href='customer.php'">Kembali</button>
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

	 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.0/dist/sweetalert2.min.js"></script>

	 <script>
		// Fungsi untuk menampilkan SweetAlert
		function showSuccessAlert() {
			// Menampilkan alert sukses
			Swal.fire({
				title: 'Berhasil!',
				text: 'Data pelanggan berhasil diupdate.',
				icon: 'success',
				confirmButtonText: 'Setuju',
				allowOutsideClick: false, // Tidak dapat klik di luar untuk menutup
				showCancelButton: false // Hanya ada tombol Setuju
			}).then((result) => {
				if (result.isConfirmed) {
					// Setelah tombol setuju ditekan, Anda bisa melakukan redirect atau aksi lainnya
					window.location.href = "customer.php"; // Contoh redirect ke halaman customer
				}
			});
		}

		function showErrorAlert() {
			// Menampilkan alert error
			Swal.fire({
				title: 'Error!',
				text: 'Terjadi kesalahan saat mengupdate data pelanggan.',
				icon: 'error',
				confirmButtonText: 'Coba Lagi',
				allowOutsideClick: false, // Tidak dapat klik di luar untuk menutup
				showCancelButton: false // Hanya ada tombol Coba Lagi
			});
		}

		// Mengecek apakah ada sukses atau error
		<?php if ($success): ?>
			showSuccessAlert(); // Tampilkan alert sukses
		<?php endif; ?>
		<?php if ($error): ?>
			showErrorAlert(); // Tampilkan alert error
		<?php endif; ?>

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
