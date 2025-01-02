<?php
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

// PAGINATION
// Inisialisasi variabel pencarian
$search = isset($_GET['search']) ? $connection->real_escape_string($_GET['search']) : '';
$search_tanggal = isset($_GET['search_tanggal']) ? $_GET['search_tanggal'] : '';

// Konfigurasi jumlah data per halaman
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Format tanggal yang diterima dari pengguna (DD-MM-YYYY) ke (YYYY-MM-DD)
if ($search_tanggal) {
    // Pastikan format tanggal valid
    $formatted_date = DateTime::createFromFormat('d-m-Y', $search_tanggal);
    if ($formatted_date) {
        // Mengubah format menjadi YYYY-MM-DD
        $formatted_date = $formatted_date->format('Y-m-d');
        $date_condition = " AND tanggal LIKE '%$formatted_date%'";
    } else {
        // Jika format salah, set kondisi pencarian kosong
        $date_condition = '';
    }
} else {
    $date_condition = ''; // Jika tidak ada tanggal yang dicari
}

// Query untuk mendapatkan data dengan pencarian
$query = "SELECT * FROM transaksi 
          WHERE (no_meteran LIKE '%$search%' OR nama_pelanggan LIKE '%$search%' OR invoice_number LIKE '%$search%' OR tanggal LIKE '%$search%') 
          $date_condition
          LIMIT $limit OFFSET $offset";

// Eksekusi query
$result = $connection->query($query);

// Hitung total data untuk pagination
$total_query = "SELECT COUNT(*) AS total FROM transaksi 
                WHERE (no_meteran LIKE '%$search%' OR nama_pelanggan LIKE '%$search%' OR invoice_number LIKE '%$search%' OR tanggal LIKE '%$search%')
                $date_condition";
$total_result = $connection->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];

// Hitung jumlah halaman
$total_pages = ceil($total_data / $limit);

// Inisialisasi ID berurut
$id = $offset + 1;

// Tentukan batas halaman yang akan ditampilkan
$adjacents = 2; // Menampilkan 2 halaman sebelumnya dan 2 halaman berikutnya

// Tentukan range halaman yang akan ditampilkan
$start_page = max(1, $page - $adjacents);
$end_page = min($total_pages, $page + $adjacents);

// Adjust the pagination range if near the start or end
if ($page <= $adjacents) {
    $end_page = min($total_pages, 5); // Jika halaman dekat awal, tampilkan 5 halaman
}
if ($page >= $total_pages - $adjacents) {
    $start_page = max(1, $total_pages - 4); // Jika halaman dekat akhir, tampilkan 5 halaman terakhir
}
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
	<style>
	.pagination {
		display: flex;
		justify-content: center;
		align-items: center;
		margin-top: 20px;
		padding: 0;
		list-style: none;
	}

	.pagination a {
		padding: 10px 16px;
		margin: 0 5px;
		text-decoration: none;
		color: #007bff;
		border: 1px solid #ddd;
		border-radius: 6px;
		font-size: 14px;
		font-weight: bold;
		background-color: #f8f9fa;
		transition: all 0.3s ease-in-out;
		box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
	}

	.pagination a.active {
		color: #fff;
		background-color: #007bff;
		border-color: #007bff;
		box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.2);
		pointer-events: none; /* Halaman aktif tidak bisa diklik */
	}

	.pagination a:hover {
		background-color: #e2e6ea;
		border-color: #ced4da;
		color: #0056b3;
	}

	.pagination a.disabled {
		color: #aaa;
		background-color: #f8f9fa;
		border-color: #ddd;
		pointer-events: none; /* Nonaktifkan tombol */
		cursor: not-allowed;
	}
	</style>
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Report</title>
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
				<i class='bx bxs-dashboard'></i>
				<span class="text">Dashboard</span>
			</a>
			</li>
			<li>
				<a href="belitoken.php" class="btn" onclick="showLoadingAndRedirect(event, 'belitoken.php')">
					<i class='bx bxs-zap'></i>
					<span class="text">Token</span>
				</a>
			</li>
			<li>
				<a href="customer.php" class="btn" onclick="showLoadingAndRedirect(event, 'customer.php')">
					<i class='bx bxs-user'></i>
					<span class="text">Data Customer</span>
				</a>
			</li>
			<li class="active">
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
				<i class='bx bxs-cog'></i>
				<span class="text">Settings</span>
			</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
			<a href="logout.php" class="logout btn" onclick="showLoadingAndRedirect(event, 'logout.php')">
				<i class='bx bxs-log-out-circle'></i>
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
			<form action="">
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
					<h1>Report Transaksi</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Report</a>
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
				<div class="head-title" style="display: flex; justify-content: flex-end; margin-right: 40px;">
					<div class="">
						<br>
						<a href="report-export.php" class="btn-download" value="Export Excel">
							<i class='bx bxs-cloud-download' ></i>
							<span class="text">Report Transaksi</span>
						</a>
					</div>
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
					<div class="head">
						<h3>Report Transaksi</h3>
						<form action="" method="GET" style="margin-right: 20px;">
							<div class="form-input" style="display: flex; align-items: center; border: 2px solid #ddd; border-radius: 30px; overflow: hidden; background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
								<input 
									type="search" 
									name="search" 
									placeholder="Search..." 
									style="flex: 1; border: none; outline: none; padding: 5px 20px; font-size: 16px; border-radius: 30px 0 0 30px;"
									value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
								<button type="submit" class="search-btn" style="background-color: #3c91e6; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 18px; border-radius: 0 30px 30px 0; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s;">
									<i class='bx bx-search'></i>
								</button>
							</div>
						</form>
					</div>
					<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>Tanggal</th>
								<th>Nama Pelanggan</th>
								<th>No Meteran</th>
								<th>Nominal</th>
								<th>Jumlah KWh</th>
								<th>Invoice Number</th>
								<th>Dibuat Oleh</th>
								<th>Action</th>
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

									echo "<tr>";
									echo "<td style='margin-top: 10px;'>{$id}.</td>";
									echo "<td>{$formatted_date}</td>";
									echo "<td>{$nama_pelanggan}</td>";
									echo "<td>{$no_meteran}</td>";
									echo "<td>{$formatted_nominal}</td>";
									echo "<td>{$row['jumlah_kwh']}</td>";
									echo "<td>{$invoice_number}</td>";
									echo "<td>{$created_by}</td>";
									echo "<td>
											<form action='invoice.php' method='get'>
												<input type='hidden' name='invoice_number' value='{$invoice_number}'>
												<button type='submit' class='btn btn-success' title='Print Invoice'>
													<i class='bx bxs-printer'></i>
												</button>
											</form>
										</td>";
									echo "</tr>";
									$id++; // Increment nomor urut
								}
							} else {
								echo "<tr><td colspan='9'>No Data Found</td></tr>";
							}
							?>
						</tbody>
					</table>
					<!-- PAGINATION -->
					<div class="pagination">
						<!-- Prev Button -->
						<?php if ($page > 1): ?>
							<a class="page-link" href="?search=<?php echo urlencode($search); ?>&search_tanggal=<?php echo urlencode($search_tanggal); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
								Prev
							</a>
						<?php endif; ?>

						<!-- Page Numbers -->
						<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
							<a class="page-link <?php echo $i == $page ? 'active' : ''; ?>" href="?search=<?php echo urlencode($search); ?>&search_tanggal=<?php echo urlencode($search_tanggal); ?>&page=<?php echo $i; ?>">
								<?php echo $i; ?>
							</a>
						<?php endfor; ?>

						<!-- Next Button -->
						<?php if ($page < $total_pages): ?>
							<a class="page-link" href="?search=<?php echo urlencode($search); ?>&search_tanggal=<?php echo urlencode($search_tanggal); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
								Next
							</a>
						<?php endif; ?>
					</div>
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
	<script src="script/main.js"></script>
	<script src="script.js"></script>

</body>
</html>
