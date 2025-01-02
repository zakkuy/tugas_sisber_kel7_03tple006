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

$query = "SELECT id, no_meteran, nama_pelanggan, kontak_pelanggan, tipe_rumah, alamat, id_tarif, daya FROM customer";
$result = $connection->query($query);

// HAPUS DATA
// Cek jika parameter untuk menghapus data ada dan sudah konfirmasi
if (isset($_GET['no_meteran']) && isset($_GET['nama_pelanggan'])) {
    $no_meteran = $_GET['no_meteran'];
    $nama_pelanggan = $_GET['nama_pelanggan'];
    
    // Jika sudah konfirmasi penghapusan
    if (isset($_GET['confirm_delete']) && $_GET['confirm_delete'] == 'yes') {
        // Query untuk menghapus data
        $query = "DELETE FROM customer WHERE no_meteran = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $no_meteran);

        if ($stmt->execute()) {
            // Jika berhasil, redirect dengan parameter success
            $success = "Data customer dengan No Meteran $no_meteran Atas Nama $nama_pelanggan berhasil dihapus.";
            header("Location: customer.php?success=" . urlencode($success));
            exit(); // Pastikan tidak ada pengalihan lebih lanjut
        } else {
            // Jika gagal, redirect dengan parameter error
            $error = "Data gagal dihapus.";
            header("Location: customer.php?error=" . urlencode($error));
            exit(); // Pastikan tidak ada pengalihan lebih lanjut
        }

        $stmt->close();
    }
}

// PAGINATION
// Inisialisasi variabel pencarian
$search = isset($_GET['search']) ? $connection->real_escape_string($_GET['search']) : '';

// Konfigurasi jumlah data per halaman
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk mendapatkan data dengan pencarian
$query = "SELECT * FROM customer WHERE no_meteran LIKE '%$search%' OR nama_pelanggan LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result = $connection->query($query);

// Hitung total data untuk pagination
$total_query = "SELECT COUNT(*) AS total FROM customer WHERE no_meteran LIKE '%$search%' OR nama_pelanggan LIKE '%$search%'";
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

// Inisialisasi pesan error
$error = '';
$success = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
	<!-- CSS LAMGSUNGAN -->
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

	 /* Animasi fade out */
    .fade-out {
        animation: fadeOut 1s forwards; /* Durasi 1 detik */
    }

    @keyframes fadeOut {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            visibility: hidden;
        }
    }
	</style>

	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Customer</title>
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
			<li class="active">
				<a href="customer.php" class="btn" onclick="showLoadingAndRedirect(event, 'customer.php')">
					<i class='bx bxs-user'></i>
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
					<h1>Data Customer</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Customer</a>
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
				<a href="customer-export.php" class="btn-download" value="Export Exel">
					<i class='bx bxs-cloud-download' ></i>
					<span class="text">Data Customer</span>
				</a>
			</div>

			<!-- Display success or error messages -->
			<div class="container mt-4">
				<?php if (isset($_GET['success'])): ?>
					<div class="alert alert-success">
						<?php echo htmlspecialchars($_GET['success']); ?>
					</div>
				<?php endif; ?>
				<?php if (isset($_GET['error'])): ?>
					<div class="alert alert-danger">
						<?php echo htmlspecialchars($_GET['error']); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="head-title">
				<div class="left">
					<br>
					<a href="tambahcustomer.php" class="btn-download btn" onclick="showLoadingAndRedirect(event, 'tambahcustomer.php')">
						<i class='bx bxs-plus-circle' ></i>
						<span class="text">Tambah Customer</span>
					</a>
				</div>
			</div>
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Data Customer</h3>
						<form action="customer.php" method="GET" style="margin-right: 20px;">
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
								<th>No Meteran</th>
								<th>Nama Pelanggan</th>
								<th>Kontak</th>
								<th>Tipe Rumah</th>
								<th>Alamat</th>
								<th>Tarif</th>
								<th>Daya</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($result->num_rows > 0) {
								while ($row = $result->fetch_assoc()) {
									echo "<tr>";
									echo "<td>" . $id . ".</td>"; // ID urut
									echo "<td>{$row['no_meteran']}</td>";
									echo "<td>{$row['nama_pelanggan']}</td>";
									echo "<td>{$row['kontak_pelanggan']}</td>";
									echo "<td>{$row['tipe_rumah']}</td>";
									echo "<td>{$row['alamat']}</td>";
									echo "<td>{$row['id_tarif']}</td>";
									echo "<td>{$row['daya']}</td>";
									echo "<td>
											<form action='updatecustomer.php' method='get' style='display: inline;'>
												<input type='hidden' name='no_meteran' value='{$row['no_meteran']}'>
												<button type='submit' class='btn btn-success' title='Update Customer'>
													<i class='bx bxs-edit'></i>
												</button>
											</form>
											<a href='javascript:void(0);' onclick='confirmDelete(\"{$row['no_meteran']}\", \"{$row['nama_pelanggan']}\")' class='btn-icon btn-icon-danger' title='Delete'>
												<i class='bx bxs-trash'></i>
											</a>
										</td>";
									echo "</tr>";

									$id++; // Increment ID
								}
							} else {
								echo "<tr><td colspan='9'>No Data Found</td></tr>";
							}
							?>
						</tbody>

					</table>
					<!-- Pagination -->
					<div class="pagination">
						<?php if ($page > 1): ?>
							<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="prev">Prev</a>
						<?php endif; ?>

						<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
							<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
								<?php echo $i; ?>
							</a>
						<?php endfor; ?>

						<?php if ($page < $total_pages): ?>
							<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="next">Next</a>
						<?php endif; ?>
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

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>

	<script>
		function confirmDelete(no_meteran, nama_pelanggan) {
			Swal.fire({
				title: 'Yakin ingin menghapus data?',
				text: 'Data customer dengan No Meteran ' + no_meteran + ' Atas Nama ' + nama_pelanggan,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, hapus!',
				cancelButtonText: 'Batal',
				reverseButtons: true
			}).then((result) => {
				if (result.isConfirmed) {
					// Jika konfirmasi, redirect ke halaman untuk menghapus
					window.location.href = 'customer.php?no_meteran=' + no_meteran + '&nama_pelanggan=' + nama_pelanggan + '&confirm_delete=yes';
				} else {
					// Jika batal, kembali ke halaman customer
					window.location.href = 'customer.php';
				}
			});
		}

		// Fungsi untuk menambahkan kelas fade-out setelah 3 detik dan menghapus elemen
		setTimeout(function() {
			var alerts = document.querySelectorAll('.alert');
			alerts.forEach(function(alert) {
				alert.classList.add('fade-out'); // Menambahkan kelas untuk animasi fade-out
				
				// Menghapus elemen setelah animasi selesai (1 detik)
				setTimeout(function() {
					alert.remove(); // Menghapus elemen dari DOM setelah animasi selesai
				}, 500); // 1000ms = 1 detik setelah fade-out selesai
			});
		}, 1000); // 3000ms = 3 detik

	</script>



</body>
</html>

