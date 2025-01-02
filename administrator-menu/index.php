<?php
session_start();
include '../connection.php';

// Atur zona waktu
date_default_timezone_set('Asia/Jakarta');

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

// Ambil pesan selamat datang dari session
$welcomeMessage = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : '';
// Hapus pesan dari session setelah mengambil
unset($_SESSION['welcome_message']);

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

// Jika tidak ada data yang ditemukan, arahkan ke halaman login
if (!$user_data) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Misalnya arahkan ke halaman login
    exit();
}

// Ambil IP pengguna
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Update waktu login dan IP pengguna di database
$currentTime = date('Y-m-d H:i:s');
$updateLogin = $connection->prepare("UPDATE users SET last_login = ?, last_ip = ? WHERE id = ?");
$updateLogin->bind_param("ssi", $currentTime, $ipAddress, $userId);
$updateLogin->execute();

// Menutup statement setelah selesai
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Dashboard</title>

	<style>
		/* Style untuk elemen select */
		select {
			width: 100%; /* Membuat dropdown mengambil lebar penuh kontainer */
			padding: 10px; /* Menambahkan padding di dalam dropdown */
			border: 1px solid #ccc; /* Warna border abu-abu terang */
			border-radius: 5px; /* Membuat sudut dropdown menjadi melengkung */
			font-size: 16px; /* Ukuran font */
			background-color: #f9f9f9; /* Warna latar belakang dropdown */
			cursor: pointer; /* Mengubah kursor menjadi pointer saat hover */
		}

		/* Style untuk opsi dropdown */
		option {
			padding: 10px; /* Menambahkan padding di dalam opsi dropdown */
		}

		/* Menambahkan efek hover */
		select:hover {
			border-color: #888; /* Mengubah warna border saat hover */
		}

		/* Style untuk state fokus */
		select:focus {
			border-color: #007bff; /* Mengubah warna border saat dropdown dalam fokus */
			outline: none; /* Menghilangkan outline default browser */
		}

		.head a {
		color: inherit; /* Menggunakan warna teks dari elemen induk (h3) */
		text-decoration: none; /* Menghapus garis bawah pada link */
		}

		/* Menjaga gaya link saat hover atau fokus */
		.head a:hover, .head a:focus {
			color: inherit; /* Menggunakan warna teks dari elemen induk saat hover atau fokus */
			text-decoration: none; /* Menghapus garis bawah saat hover atau fokus */
		}
		.pagination {
		display: flex;
		justify-content: center;
		margin-top: 20px;
		}

		.pagination a {
			text-decoration: none;
			color: #007bff; /* Warna link */
			padding: 10px 15px;
			border: 1px solid #ddd; /* Garis batas */
			margin: 0 5px;
			border-radius: 5px; /* Sudut membulat */
			transition: background-color 0.3s, color 0.3s; /* Efek transisi */
		}

		.pagination a:hover {
			background-color: #f8f9fa; /* Warna latar belakang saat hover */
			color: #0056b3; /* Warna teks saat hover */
		}

		.pagination .active {
			background-color: #007bff; /* Warna latar belakang untuk halaman aktif */
			color: #fff; /* Warna teks untuk halaman aktif */
			border: 1px solid #007bff; /* Garis batas halaman aktif */
		}

		.pagination .disabled {
			color: #ccc; /* Warna teks untuk link yang dinonaktifkan */
			border-color: #ccc; /* Garis batas untuk link yang dinonaktifkan */
			pointer-events: none; /* Menonaktifkan klik pada link */
		}
	</style>

</head>
<body>
	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="index.php" class="brand btn" onclick="showLoadingAndRedirect(event, 'index.php')">
			<i class='bx bxs-like'></i>
			<span class="text">NexLit</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
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
					<h1>Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Home</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
				<a href="belitoken.php">
					<li>
						<i class='bx bxs-zap'></i>
							<span class="text">
								<p>Pembelian Token</p>
							</span>
					</li>
				</a>

				<a href="customer.php">
					<li>
						<i class='bx bxs-user' style="color: #ffce26; background-color: #FFF2C6;" ></i>
							<span class="text">
								<p>Data Customer</p>
							</span>
					</li>
				</a>
				<a href="report.php">
					<li>
						<i class='bx bxs-report' style="color: #fd7238; background-color: #ffe0d3;"></i>
							<span class="text">
								<p>Report</p>
							</span>
					</li>
				</a>
			</ul>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<!-- Animasi Loading -->
	<div class="loading-overlay">
		<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
	 </div>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="script.js"></script>

	<!-- Menampilkan alert jika ada pesan -->
    <?php if (!empty($welcomeMessage)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Selamat Datang!',
            text: '<?php echo $welcomeMessage; ?> !'
        });
    </script>
    <?php endif; ?>
</body>
</html>