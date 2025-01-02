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

// Inisialisasi pesan error
$error = '';
$success = '';

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $fullname = mysqli_real_escape_string($connection, $_POST['fullname']);
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($connection, $_POST['confirmPassword']);
    
    // Validasi password dan konfirmasi password
    if ($password !== $confirmPassword) {
        $error = 'Password dan Konfirmasi Password tidak cocok.';
    } else {
        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Cek jika username atau email sudah terdaftar
        $checkUserQuery = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $checkResult = mysqli_query($connection, $checkUserQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = 'Username atau Email sudah terdaftar.';
        } else {
			$password = $_POST['password']; // Password dari input form
			$hashedPassword = hash('sha256', $password); // Meng-hash password menggunakan SHA-256


            $createOn = date('Y-m-d'); // Mendapatkan tanggal saat ini dalam format YYYY-MM-DD

			// Menetapkan nilai default untuk $about jika tidak ada input
			$about = isset($_POST['about']) ? mysqli_real_escape_string($connection, $_POST['about']) : NULL;

			// Query untuk menambahkan admin baru
			$insertQuery = "INSERT INTO users (fullname, username, email, password, role, about, create_on) 
                			VALUES ('$fullname', '$username', '$email', '$hashedPassword', 'Administrator', '$about', '$createOn')";
            
            if (mysqli_query($connection, $insertQuery)) {
                $success = 'Admin berhasil ditambahkan.';
            } else {
                $error = 'Terjadi kesalahan, silakan coba lagi.';
            }
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
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Tambah Admin</title>
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
			<li class="active">
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
					<h1>Tambah Data Administrator</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Add Administrator</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="text-decoration-none" href="administrator.php">Administrator</a>
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
                    
				<form method="POST">
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputFullname">Fullname</label>
							<input type="text" class="form-control" id="inputFullname" name="fullname" placeholder="Masukkan Fullname" required>
						</div>
						<div class="form-group col-md-6">
							<label for="inputUsername">Username</label>
							<input type="text" class="form-control" id="inputUsername" name="username" placeholder="Masukkan Username" required>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputEmail">Email</label>
							<input type="email" class="form-control" id="inputEmail" name="email" placeholder="Masukkan Email Address" required>
						</div>
						<div class="form-group col-md-6">
							<label for="inputRole">Role</label>
							<input type="text" class="form-control" id="inputRole" value="Administrator" disabled>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputPassword">Password</label>
							<input type="password" class="form-control" id="inputPassword" name="password" placeholder="Masukkan Password" required>
						</div>
						<div class="form-group col-md-6">
							<label for="inputConfirmPassword">Confirm Password</label>
							<input type="password" class="form-control" id="inputConfirmPassword" name="confirmPassword" placeholder="Masukkan Confirm Password" required>
						</div>
					</div>
					<button type="submit" class="btn">Tambah Data</button>
					<button type="button" class="btn" onclick="showLoadingAndRedirect('administrator.php')">Cancel</button>
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
	<script src="script.js"></script>

</body>
</html>
