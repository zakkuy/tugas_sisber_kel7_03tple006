<?php
session_start();
include "../connection.php"; // Koneksi ke database

// Validasi Cookie dan Session
$profilePicture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'profile/default.png';

if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php");
    exit();
}

// Ambil ID pengguna dari session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php");
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

// Inisialisasi pesan error dan sukses
$error = '';
$success = '';

// Memproses request POST dari AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil nomor meteran dari request
    $request = json_decode(file_get_contents('php://input'), true); // Parsing JSON dari AJAX
    $no_meteran = $request['no_meteran'] ?? '';

    // Validasi input nomor meteran
    if (empty($no_meteran)) {
        echo json_encode(['success' => false, 'message' => 'Nomor meteran tidak boleh kosong!']);
        exit();
    }

    // Cek nomor meteran di database
    $stmt = $connection->prepare("SELECT * FROM customer WHERE no_meteran = ?");
    $stmt->bind_param("s", $no_meteran);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika nomor meteran ditemukan, simpan data ke session
        $data = $result->fetch_assoc();
        $_SESSION['confirm_token_data'] = $data; // Simpan data pelanggan untuk e0edd1921396030fbf2845547212d9abe481995ae659fa1c5f8ade977ed190bf.php

        echo json_encode(['success' => true]); // Kirim respon sukses
    } else {
        // Jika nomor meteran tidak ditemukan
        echo json_encode(['success' => false, 'message' => 'Nomor meteran tidak ditemukan!']);
    }

    $stmt->close();
    exit();
}
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
	<title>NexLit Token</title>
</head>
<body>

	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="index.php" class="brand btn" onclick="showLoadingAndRedirect(event, 'index.php')">
			<i class='bx bxs-like'></i>
			<span class="text">NexLit</span>
		</a>
		<ul class="side-menu top">
			<li >
			<a href="index.php" class="btn" onclick="showLoadingAndRedirect(event, 'index.php')">
				<i class='bx bxs-dashboard'></i>
				<span class="text">Dashboard</span>
			</a>
			</li>
			<li class="active">
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
					<h1>Pembelian Token Listrik</h1>
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
                    <form id="form-cari">
                        <div class="form-row">
                            <div class="form-group col-md-6">
								<label for="inputno_meteran">Masukkan Nomor Meteran</label>
								<input type="text" class="form-control" id="inputno_meteran" placeholder="Masukkan No Meteran">
							</div>
                            <div class="form-group col-md-6">
                            </div>
                        </div>
						<button type="button" class="btn" onclick="window.location.href='index.php'">Cancel</button>
                    	<button type="submit" class="btn">Cari Data</button>
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

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const formCari = document.getElementById('form-cari');

        formCari.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah reload halaman

            const noMeteran = document.getElementById('inputno_meteran').value;
            if (!noMeteran) {
                Swal.fire('Error', 'Nomor meteran tidak boleh kosong!', 'error');
                return;
            }

            // Kirim data ke server melalui AJAX
            fetch('belitoken.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Pastikan header ini benar
                },
                body: JSON.stringify({ no_meteran: noMeteran }), // Kirim data dalam format JSON
            })
            .then(response => response.json()) // Parsing response JSON
            .then(data => {
                if (data.success) {
                    window.location.href = 'e0edd1921396030fbf2845547212d9abe481995ae659fa1c5f8ade977ed190bf.php'; // Redirect ke halaman e0edd1921396030fbf2845547212d9abe481995ae659fa1c5f8ade977ed190bf.php
                } else {
                    Swal.fire('Error', data.message || 'Nomor meteran tidak ditemukan!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan pada server!', 'error');
            });
        });
    </script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="script.js"></script>

</body>
</html>
